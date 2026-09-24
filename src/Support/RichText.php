<?php

namespace ME\MerchandisingTrace\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Summernote-authored fields (remarks, comments, descriptions, ...) are
 * stored as HTML. Everything that echoes them goes through clean() — a
 * DOM-based whitelist, so a pasted <script> or onclick= never reaches the
 * page — and plain() where only text makes sense (Excel, table cells).
 * Values saved before the editor existed are plain text; clean() keeps
 * their line breaks.
 */
final class RichText
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'sub', 'sup', 'span', 'div', 'font',
        'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'hr',
        'table', 'thead', 'tbody', 'tr', 'td', 'th',
    ];

    private const ALLOWED_ATTRIBUTES = ['href', 'target', 'style', 'color', 'face', 'size', 'colspan', 'rowspan'];

    /** Tags whose content is dropped along with the tag itself. */
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template', 'svg', 'math'];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        if (! preg_match('#<\s*/?\s*[a-z][^>]*>#i', $html)) {
            return nl2br(e($html));
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__rt">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__rt') ?? $doc->documentElement;
        if (! $root) {
            return nl2br(e(strip_tags($html)));
        }

        self::sanitizeChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    public static function plain(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $text = preg_replace('#<br\s*/?>|</p>|</div>|</li>#i', "\n", $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        // Iterate over a snapshot — we mutate the live list below.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            self::sanitizeChildren($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unwrap: keep the (already sanitized) children, lose the tag.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->name);
                $value = trim($attr->value);

                $keep = in_array($name, self::ALLOWED_ATTRIBUTES, true)
                    && ! ($name === 'href' && ! preg_match('#^(https?:|mailto:|/|\#)#i', $value))
                    && ! ($name === 'style' && preg_match('#expression|url\s*\(|javascript:|behavior#i', $value));

                if (! $keep) {
                    $child->removeAttribute($attr->name);
                }
            }

            if ($tag === 'a' && $child->hasAttribute('target')) {
                $child->setAttribute('target', '_blank');
                $child->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }
}
