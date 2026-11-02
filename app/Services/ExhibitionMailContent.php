<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class ExhibitionMailContent
{
    public function clean(string $html): string
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?><div id="mail-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('mail-root');
        if (! $root) {
            return '';
        }
        foreach (iterator_to_array($root->childNodes) as $child) {
            $this->sanitize($child);
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function sanitize(DOMNode $node): void
    {
        if (! $node instanceof DOMElement) {
            if ($node->nodeType !== XML_TEXT_NODE) {
                $node->parentNode?->removeChild($node);
            }
            return;
        }

        $allowed = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'div'];
        $tag = strtolower($node->tagName);
        if (! in_array($tag, $allowed, true)) {
            $node->parentNode?->removeChild($node);
            return;
        }

        foreach (iterator_to_array($node->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);
            $valid = ($tag === 'a' && $name === 'href' && preg_match('~^https?://~i', $value))
                || ($tag === 'img' && $name === 'src' && preg_match('~^https?://~i', $value))
                || ($tag === 'img' && $name === 'alt');
            if (! $valid) {
                $node->removeAttributeNode($attribute);
            }
        }
        if ($tag === 'a') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
        if ($tag === 'img' && ! $node->hasAttribute('src')) {
            $node->parentNode?->removeChild($node);
            return;
        }
        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->sanitize($child);
        }
    }
}
