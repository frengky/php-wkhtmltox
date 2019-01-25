<?php

namespace Frengky\WkHtml;

class PDF extends WkHtml
{
    protected static $executable = 'wkhtmltopdf'; 

    protected function __construct() {
        //
    }
}