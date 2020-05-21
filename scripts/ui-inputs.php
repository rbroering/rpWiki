<?php

class UiInputs extends HTML {
    private $HTML;


    public function __construct() {
        $this->HTML = new HTMLTags();
        $this->HTML->setPrintMode(false);
		$this->HTML->setAutoNl(true);
        $this->HTML->setAutoIndent(true);
    }


    /**
     * Generate a single checkbox.
     *
     * @param   string  $id
     * @param   array   $data
     * @return  string|false
     */
    public function checkbox($id, $data) {
		$HTML   = $this->HTML;
        $output = "";

        if (
            !isset($data['checked']) ||
            empty($data['checked']) ||
            (
                !is_bool($data['checked']) &&
                !is_numeric($data['checked'])
            )
        )
            $data['checked'] = false;

        if (!array_key_exists('class', $data) || empty($data['class'])) {
            $data['class'] = [];
        } else {
            if (is_string($data['class'])) {
                $data['class'] = explode(' ', $data['class']);
            }
        }

        if (!isset($data['name'])) $data['name'] = $id;

        $Settings = [
            'type'		=> 'checkbox',
            'name'		=> $data['name'],
            'id'		=> $id,
            'class'		=> $HTML->class(
                array_merge(['check-hidden'], $data['class'])
            ),
            'checked'	=> ($data['checked']) ? 'true' : 'false'
        ];

        if (isset($data['disabled']) && $data['disabled'])
            $Settings['disabled'] = 'true';

        if (!isset($data['label']) || !is_string($data['label'])) $data['label'] = $id;

        $output .= $HTML->divClass('checkbox',
            $HTML->tag('input', $Settings).
            $HTML->tag('label', [
                'for'			=> $id,
                'id'			=> $id . '_label',
                'class'			=> $HTML->class([
                    'check-label',
                    ($data['checked']) ? 'checked' : 'unchecked',
                    (array_key_exists('disabled', $data)) ? 'disabled' : ''
                ]),
                'data-checked'	=> ($data['checked']) ? 'checked' : 'unchecked'
            ],
                $HTML->divId().
                $HTML->span($data['label'], ['label-desc'])
            )
        );

        return $this->returnOrPrint($output);
    }


    /**
     * Generate a list of checkboxes.
     *
     * @param   array   $fetch
     * @param   string  $before
     * @param   string  $after
     * @return  string|false
     */
    public function checkboxList($fetch, $before = "", $after = "") {
		$HTML   = $this->HTML;
        $output = "";

        $this->overridePm = true;
		foreach ($fetch as $id => $data) {
            $output .= $before . $this->checkbox($id, $data) . $after;
        }
        $this->overridePm = false;

        return $this->returnOrPrint($output);
	}
}

$UI_Inputs = new UiInputs();