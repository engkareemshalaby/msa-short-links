{{ trim(html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '</li>'], "\n", $bodyHtml)))) }}
