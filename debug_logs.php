<?php
\ = 'storage/logs/laravel.log';
if (!file_exists(\)) die('No log');
\ = file(\);
echo implode('', array_slice(\, -100));

