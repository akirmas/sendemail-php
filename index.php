<?php
require_once(__DIR__.'/utils/http.php');
use function \http\{input, output};
require_once(__DIR__.'/main.php');
echo output(main(input()));
