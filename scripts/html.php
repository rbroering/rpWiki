<?php

define("HTML_CALLED_FROM_WITHIN_CLASS", "The method was called from within the HTML class. #d672jaO9_1iJ");

class HTML {
    protected $printMode  = false;
    protected $overridePm = false;
    protected $autoNl     = false;
    protected $overrideNl = false;
    protected $autoTab    = false;
    protected $overrideTab= false;
    protected $shortTags  = [];
    protected $tagList    = [];
    protected $tagStats   = [];
    protected $lastElem   = false;
    private $Errors       = [];
    private $Warnings     = [];

    protected $noValueAttributes = [
        'checked',
        'disabled'
    ];

    protected $noNlTags    = [
        'span', 'b', 'i', 'u', 'strong', 's',
        'div' => [
            'attributes' => [
                'contenteditable' => true
            ],
            'action' => [
               'open'
            ]
        ],
        'textarea' => [
            'action' => [
                'open'
            ]
        ],
        'a' => [
            'attributes' => [
                'name' => true
            ],
            'action' => [
                'open'
            ]
        ]
    ];

    protected $noIndent    = [
        'span', 'b', 'i', 'u', 'strong', 's',
        'div' => [
            'attributes' => [
                'contenteditable' => true
            ],
            'action' => [
               'close'
            ]
        ],
        'textarea' => [
            'action' => [
                'close'
            ]
        ],
        'a' => [
            'attributes' => [
                'name' => true
            ],
            'action' => [
                'close'
            ]
        ]
    ];

    public $indent      = 0;

    public function __construct() {
        $this->shortTags = [
            'input', 'link', 'img', 'br'
        ];
    }


    /**
     * Automatically create statistics for used tags.
     *
     * @param string $tag_name
     * @return void
     */
    protected function tagStats($tag_name) {
        if (!array_key_exists($tag_name, $this->tagStats)) {
            $this->tagStats[$tag_name]          = [];

            $this->tagStats[$tag_name]['count'] = 0;
        }

        $this->tagStats[$tag_name]['count']++;
    }


    /**
     * If activated, print out HTML instead of returning it.
     * If so, return false.
     *
     * @param bool $status
     * @return void
     */
    public function setPrintMode($status) {
        $status = ($status) ? true : false;
        $this->printMode = $status;
    }


    /**
     * Switch automatic newline insertion on or off.
     *
     * @param bool $status
     * @return void
     */
    public function setAutoNl($status) {
        $status = ($status) ? true : false;
        $this->autoNl = $status;
    }


    /**
     * Switch automatic indentation on or off. Set base indentation.
     *
     * @param bool $status
     * @param int|bool $indent
     * @return void
     */
    public function setAutoIndent($status, $indent = false) {
        $status = ($status) ? true : false;
        $this->autoTab = $status;

        if (is_integer($indent))
            $this->indent = $indent;
    }


    /**
     * Check whether to use autoNewline and autoIndentation for given tag.
     *
     * @param string $tag_name
     * @param array $tag_attributes
     * @param string $action
     * @return bool
     */
    protected function useNlAndIndent($switch, $tag_name = "", $tag_attributes = [], $action = "") {
        $checks = true;

        switch ($switch) {
            case 'newline':
            default:
                $array = $this->noNlTags;
            break;
            case 'indent':
                $array = $this->noIndent;
        }

        if ($tag_name && in_array($tag_name, $array)) $checks = false;
        if ($tag_name && array_key_exists($tag_name, $array)) {
            $tag_settings = $array[$tag_name];

            if (array_key_exists('attributes', $tag_settings)) {
                $attributes = $tag_settings['attributes'];

                foreach ($tag_attributes as $key => $value) {
                    if (array_key_exists($key, $attributes)) {
                        if ($attributes[$key] === true || $attributes[$key] === $value)
                            $checks = false;
                    }
                }

                if (array_key_exists('action', $tag_settings)) {
                    if (!in_array($action, $tag_settings['action']))
                        $checks = true;
                }
            }
            if (array_key_exists('action', $tag_settings)) {
                if (in_array($action, $tag_settings['action']))
                    $checks = false;
            }
        }

        return $checks;
    }


    /**
     * Insert a newline character.
     *
     * @return string
     */
    protected function autoNl($tag_name = false, $tag_attributes = [], $action = "") {
        $checks = $this->useNlAndIndent($tag_name, $tag_attributes, $action);

        if ($checks && $this->autoNl && !$this->overrideNl)
            return "\n";
        else
            return "";

        $this->overrideNl = false;
    }


    /**
     * Return the right amount of indentation.
     *
     * @return string
     */
    protected function autoIndent() {
        /*if (!$this->autoTab || $this->overrideTab)
            return "";*/

        $str = "";

        #echo $this->indent . ', ';

        for ($i = 1; $i <= $this->indent; $i++) {
            $str .=  "\t";
        }

        return $str;
    }


    /**
     * If Print-Mode is activated, print out HTML instead of returning it.
     * (De-)Activated via setPrintMode(). Return false if Print-Mode is enabled.
     *
     * @param string $str
     * @return string|false
     */
    protected function returnOrPrint($str) {
        if ($this->overridePm) {
            $this->overridePm = false;
            return $str;
        }

        if ($this->printMode)
            echo $str;
        if (!$this->printMode)
            return $str;

        return false;
    }


    /**
     * Generator for HTML tag attributes.
     *
     * @param array $tag_attributes
     * @return string
     */
    protected function attributes($tag_attributes) {
        $attributes = empty($tag_attributes) ? "" : " ";

        foreach ($tag_attributes as $key => $value) {
            if (
                (!empty($value) && $value && $value != "false" && $value != "off") ||
                !in_array($key, $this->noValueAttributes)
            ) {
                $attributes .= $key . "=\"" . $value . "\" ";
            }
        }

        return $attributes;
    }


    /**
     * Open a HTML tag. <like_this attr="" >
     *
     * @param string $id
     * @param string $tag_name
     * @param array $tag_attributes
     * @return string|false
     */
    public function open($id, $tag_name, $tag_attributes = []) {
        $this->tagStats($tag_name);

        $this->tagList[$id] = [
            'tag'           => $tag_name,
            'attributes'    => $tag_attributes,
            'printmodeopen' => $this->printMode,
            'closed'        => false
        ];

        $attributes = $this->attributes($tag_attributes);

        $indent = ($this->useNlAndIndent('indent', $tag_name, $tag_attributes, 'open')) ? $this->autoIndent() : '';
        $nl     = ($this->useNlAndIndent('nl', $tag_name, $tag_attributes, 'open')) ? $this->autoNl() : '';

        $this->indent++;

        return $this->returnOrPrint($indent . "<$tag_name$attributes>" . $nl);
    }


    /**
     * Close a HTML tag that has been opened via HTML->open(). </like_this>
     *
     * @param string $id
     * @param string $tag_name
     * @return string|false
     */
    public function close($id, $tag_name = "") {
        if (array_key_exists($id, $this->tagList)) {
            $tagList = $this->tagList[$id];
            $tagList['closed']          = true;
            $tagList['printmodeclose']  = $this->printMode;
            $tag_name = $tagList['tag'];

            if ($tagList['printmodeopen'] !== $tagList['printmodeclose'])
                $this->Warnings[] = "Different print modes set when opening and closing tag &lt;$tag_name&gt; (ID: <b>$id</b>).";

            $this->indent--;

            $indent = ($this->useNlAndIndent('indent', $tag_name, $this->tagList[$id]['attributes'], 'close')) ? $this->autoIndent() : '';
            $nl     = ($this->useNlAndIndent('nl', $tag_name, $this->tagList[$id]['attributes'], 'close')) ? $this->autoNl() : '';

            unset($this->tagList[$id]);

            return $this->returnOrPrint($indent . "</$tag_name>" . $nl);
        } else {
            $this->Errors[] = "Trying to close tag that has not been opened (given ID: <b>$id</b>).";
        }
    }


    /**
     * Open a HTML tag and close it. Insert $tag_inner content. <like_this attr="" >...</like_this>
     *
     * @param string $tag_name
     * @param array $tag_attributes
     * @param string $tag_inner
     * @return string|false
     */
    public function tag($tag_name, $tag_attributes = [], $tag_inner = "") {
        $attributes = $this->attributes($tag_attributes);

        if (empty($tag_inner) && in_array($tag_name, $this->shortTags)) {
            return $this->returnOrPrint("<$tag_name$attributes />");
        } else {
            $this->overridePm   = true;
            $open               = $this->open(0, $tag_name, $tag_attributes);
            $this->overridePm   = true;
            $close              = $this->close(0, $tag_name);

            return $this->returnOrPrint($open . $tag_inner . $close);
        }
    }


    /**
     * Generator for CSS classes for class attribute.
     *
     * @param array $classes
     * @return string
     */
    public function class($classes) {
        $return = "";

        foreach ($classes as $class) {
            if (!empty($class))
                $return .= $class . ' ';
        }

        $return = rtrim($return);

        return $return;
    }


    /**
     * Generator for CSS styles for style attribute.
     *
     * @param array $styles
     * @return string
     */
    public function style($styles) {
        $return = "";

        foreach ($styles as $key => $property)
            $return .= "$key: $property; ";

        $return = rtrim($return);

        return $return;
    }


    /**
     * Return text with right indentation and add a newline character.
     *
     * @param string $str
     * @return string|false
     */
    public function text($str) {
        return $this->returnOrPrint($this->autoIndent() . $str . $this->autoNl());
    }


    /**
     * Mark next element as last one created by this class.
     *
     * @return void
     */
    public function markLastElem() {
        $this->lastElem     = true;
        $this->overrideNl   = true;
    }


    public function printStats() {
        $output = "";
        $i      = 0;

        foreach ($this->tagStats as $tag => $data) {
            $i++;

            echo "HTML-Tool Stats: #$i:\t<b>$tag</b> (Counted " . $data['count'] . " times).<br />";
        }

        echo $output;
    }


    public function getErrors() {
        foreach ($this->tagList as $id => $data) {
            if (!$data['closed'])
                $this->Errors[] = "Found unclosed tag &lt;" . $data['tag'] . "&gt; created with the following id: <b>$id</b>.<br />";
        }

        foreach ($this->Errors as $i => $Error) {
            echo "<span style=\"color: darkred;\" >HTML-Tool Error #$i:\t$Error</span><br />";
        }

        foreach ($this->Warnings as $i => $Warning) {
            echo "<span style=\"color: darkorange;\" >HTML-Tool Warning #$i:\t$Warning</span><br />";
        }
    }
}