<?php

class Message {
    public $type;
    public $content;

    public function __construct($type, $content)
    {
        $this->type = $type;
        $this->content = $content;
    }

    public function output() {
        echo '<div class="'.$this->type.'">'.$this->content.'</div>';
    }
}