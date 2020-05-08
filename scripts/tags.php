<?php

class HTMLTags extends HTML {
    /**
     * Generate a simple break tag <br />.
     *
     * @param string $data
     * @return string|false
     */
    public function br($data = "") {
        return $this->returnOrPrint($this->tag('br'));
    }


    /**
     * Generate a break tag <br /> with "clear" attribute.
     *
     * @param string $clear
     * @return string|false
     */
    public function brClear($clear = "both") {
        $valid = [
            'both', 'left', 'right',
            'inline-start', 'inline-end',
            'inherit', 'initial', 'none', 'unset', 'revert'
        ];

        $clear = (in_array($clear, $valid)) ? ['clear' => $clear] : [];

        return $this->returnOrPrint($this->tag('br', $clear));
    }


    /**
     * Generate a named anchor for jump links (page#section).
     *
     * @param string $name
     * @return string|false
     */
    public function jumpAnchor($name) {
        return $this->returnOrPrint($this->tag('a', ["name" => $name]));
    }


    /**
     * Generate a link (reference).
     *
     * @param string $href
     * @param array $tag_attributes
     * @param string $tag_inner
     * @return string|false
     */
    public function a($href, $tag_attributes = [], $tag_inner = "") {
        $attributes = array_merge(['href' => $href], $tag_attributes);
        return $this->returnOrPrint($this->tag('a', $attributes, $tag_inner));
    }


    /**
     * Generate a div tag <div> that has an id, classes and other attributes.
     *
     * @param string $id
     * @param string|array $class
     * @param array $tag_attributes
     * @param string $tag_inner
     * @return string|false
     */
    public function divIdClassAttr($id = "", $class = [], $tag_attributes = [], $tag_inner) {
        if (is_string($class))
            $class = [$class];

        $class = $this->class($class);

        if (!empty($class))
            $class = ['class' => $class];

        $id = (!empty($id)) ? ['id' => $id] : [];

        $attributes = array_merge($id, $class, $tag_attributes);

        return $this->returnOrPrint($this->tag('div', $attributes, $tag_inner));
    }


    /**
    * Generate a div tag <div> that has an id and classes.
    *
    * @param string $id
    * @param string|array $class
    * @param string $tag_inner
    * @return string|false
    */
    public function divIdClass($id = "", $class = [], $tag_inner = "") {
        return $this->returnOrPrint($this->divIdClassAttr($id, $class, [], $tag_inner));
    }


    /**
     * Generate a div tag <div> that has an id.
     *
     * @param string $id
     * @param string $tag_inner
     * @param array $tag_attributes
     * @return string|false
     */
    public function divId($id = "", $tag_inner = "", $tag_attributes = []) {
        return $this->returnOrPrint($this->divIdClassAttr($id, [], $tag_attributes, $tag_inner));
    }


    /**
     * Generate a div tag <div> that has classes.
     *
     * @param string|array $class
     * @param string $tag_inner
     * @param array $tag_attributes
     * @return string|false
     */
    public function divClass($class = [], $tag_inner = "", $tag_attributes = []) {
        return $this->returnOrPrint($this->divIdClassAttr("", $class, $tag_attributes, $tag_inner));
    }


    /**
     * Generate a span tag <span>.
     *
     * @param string $text
     * @param string|array $class
     * @param array $tag_attributes
     * @return string|false
     */
    public function span($text = "", $class = [], $id = "", $tag_attributes = []) {
        if (is_string($class))
            $class = [$class];

        if (!empty($class)) {
            $class = $this->class($class);
            $class = ['class' => $class];
        }

        $id = (!empty($id)) ? ['id' => $id] : [];

        $attributes = array_merge($id, $class, $tag_attributes);

        return $this->returnOrPrint($this->tag('span', $attributes, $text));
    }
}

$HTML = new HTMLTags();