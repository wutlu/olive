<?php

$prefix = [
  'değil'
];

$prefix = array_map(function($word) {
  return str_slug($word, ' ');
}, $prefix);
