<?php

class TemplateIterator
{

    public $html;
    public $fileiterator;
    public $demo;
    public $keywords;

    public function __construct($file, $demo = false)
    {

        $this->demo = $demo;
        $this->keywords = ["background", "image", "logo", "sticker", "footer", "slogan", "paragraph", "heading", "product", "button"];

        $this->html = new DOMDocument();
        $this->html->loadHTML(file_get_contents($file));

        $this->fileiterator = new FileIterator();

        /* Copy css file to prepared folder */
        $this->fileiterator->copyAllCss();

        /* Find all nodes, change all innerHTML to {{text}}, and find files in attributes */
        $this->recursivelyFindNodes($this->html->getElementsByTagName('body')->item(0));

        /* Put all javascript files */
        $elements = $this->html->getElementsByTagName('script');
        $this->findSrc($elements);

        /* Create {{css}} in head */
        $this->createCssTag();
    }

    public function getHTML()
    {
        return $this->html;
    }

    /**
     * Save HTML
     *
     * @param string $fname
     * @return void
     */
    public function save($fname)
    {
        $this->html->formatOutput = true;
        $this->html->preserveWhiteSpace = true;
        
        if($this->demo === true) {
            $this->html->saveHTMLFile($fname);
        } else {
            $this->html->save($fname);
        }
    }

    /**
     * Find script paths and copy to code
     * <script src='jquery.min.js'></script> -> <script>...</script>
     *
     * @return void
     */
    public function findSrc($elements, $attribute = 'src')
    {
        foreach ($elements as $element) {
            if ($element->hasAttributes()) {
                foreach ($element->attributes as $attr) {
                    if ($attr->nodeName === $attribute) {
                        /*if(is_file($attr->nodeValue)) {
                            $element->textContent = \JShrink\Minifier::minify(file_get_contents($attr->nodeValue));
                            $element->removeAttribute($attribute);
                        }*/
                        $element->textContent = ' ';
                        $attr->nodeValue = $this->findFileIn($attr->nodeValue);
                    }
                }
            }
        }
    }

    /**
     * Create <style>{{css}}</style> in header
     *
     * @return void
     */
    public function createCssTag()
    {
        if($this->demo === false) {
            $head = $this->html->getElementsByTagName('head')->item(0);
            $style = $this->html->createElement('style');
            $style->setAttribute('type', 'text/css');
            $style->textContent = '{{css}}';
            $head->appendChild($style);
        } else {
            $elements = $this->html->getElementsByTagName('link');
            foreach ($elements as $element) {

                if ($element->hasAttributes()) {
                    foreach ($element->attributes as $attr) {
                        if ($attr->nodeName === 'href') {

                            $keyword = '';
                            foreach($element->parentNode->attributes as $parentAttr) {
                                $keyword = $this->findKeywordIn($parentAttr);
                            }

                            $attr->nodeValue = $this->findFileIn($attr->nodeValue, $keyword);
                        }
                    }
                }
            }
        }
    }

    /**
     * Find file in text then replace to the basename
     * url('images/image.jpg') -> {{image.jpg}}
     *
     * @param string $value
     * @return changed
     */
    private function findFileIn($value, $placeholder = '') {
        foreach ($this->fileiterator->getFiles() as $file) {

            // If keyword is empty the placeholder will the filename
            if(empty($placeholder)) {
                $placeholder = basename($file);
            }

            $result = ($this->demo === false) ? "{{{$placeholder}}}" : basename($file);
            $value = str_replace($file, $result, $value, $count);

            // Copy file to the demo directory
            if($this->demo === true and $count > 0) {
                copy($file, DEMO_DIR . basename($file));
            }
        }

        return $value;
    }

    /**
     * Find keywords
     *
     * @param string $value
     * @return void
     */
    private function findKeywordIn($value) {
        foreach($this->keywords as $keyword) {
            if(strpos($value, $keyword) !== false) {

                var_dump($keyword);
                return $keyword;
            }
        }
    }

    /**
     * Find recursive all nodes and create {{tags}} if its not a demo
     *
     * @param [type] $dom
     * @param integer $depth
     * @param integer $predecessor_depth
     * @return void
     */
    private function recursivelyFindNodes($dom, $depth = 1, $predecessor_depth = 0)
    {

        $return = array();

        foreach ($dom->childNodes as $element) {
            switch ($element->nodeType) {
                case XML_TEXT_NODE:
                    $return[] = array (
                        'absolute_depth' => $depth,
                        'relative_depth' => $depth - $predecessor_depth,
                        'value' => $element->nodeValue
                    );

                    // Change innerHTML to {{text}}
                    if($this->demo === false) {
                        if (trim($element->nodeValue) !== '') {

                            $keyword = '';

                            // Find keywords in parent element attributes first
                            foreach($element->parentNode->attributes as $parentAttr) {
                                $keyword = $this->findKeywordIn($parentAttr->nodeValue);
                            }

                            // Find in the elements if keyword not found in parent element
                            if(empty($keyword)) {
                                foreach($element->attributes as $attr) {
                                    $keyword = $this->findKeywordIn($attr->nodeValue);
                                }
                            }
                            
                            // 
                            if(empty($element->nodeValue)) {
                                $keyword = $element->nodeValue;
                            }
                            

                            $element->nodeValue = "{{{$keyword}}}";
                            $predecessor_depth = $depth;
                        }
                    }

                    // Find files in all attributes
                    if($element->hasAttributes()) {
                        foreach($element->attributes as $attr) {

                            $keyword = '';

                            // Find keywords in parent element attributes first
                            foreach($element->parentNode->attributes as $parentAttr) {
                                $keyword = $this->findKeywordIn($parentAttr->nodeValue);
                            }

                            // Find in the elements if keyword not found in parent element
                            if(empty($keyword)) {
                                $keyword = $this->findKeywordIn($attr->nodeValue);
                            }

                            $attr->nodeValue = $this->findFileIn($attr->nodeValue);
                        }
                    }
                break;

                case XML_ELEMENT_NODE:
                    $return[] = array (
                        'absolute_depth' => $depth,
                        'relative_depth' => $depth - $predecessor_depth,
                        'value' => $element
                    );

                    // Find files in all attributes
                    if($element->hasAttributes()) {
                        foreach($element->attributes as $attr) {

                            $keyword = '';

                            // Find keywords in parent element attributes first
                            foreach($element->parentNode->attributes as $parentAttr) {
                                $keyword = $this->findKeywordIn($parentAttr->nodeValue);
                            }

                            // Find in the elements if keyword not found in parent element
                            if(empty($keyword)) {
                                $keyword = $this->findKeywordIn($attr->nodeValue);
                            }

                            $attr->nodeValue = $this->findFileIn($attr->nodeValue, $keyword);
                        }
                    }

                    $child_return_value = $this->recursivelyFindNodes($element, $depth + 1, $predecessor_depth);
                    $return = array_merge($return, $child_return_value);
                    $predecessor_depth = $return[count($return) - 1]['absolute_depth'];
                break;
            }
        }

        return $return;
    }

}
