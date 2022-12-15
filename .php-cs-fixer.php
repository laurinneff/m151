<?php

declare(strict_types=1);

$config = new PhpCsFixer\Config();
return  $config->setRules([
  '@PER' => true,
  'declare_strict_types' => true,
]);
