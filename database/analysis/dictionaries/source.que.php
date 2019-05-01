<?php

$que = [
  'kimim',
  'kimin',
  'kimler',
  'kimlerin',
  'kimse',
  'kim',
  'neden',
  'nedir',
  'nasıl',
  'yardım',
  'eder',
  'misin',
  'misiniz',
  'miyim',
  'miyiz',
  'mı',
  'mu',
  'muyum',
  'musun',
  'muyuz',
  'muyuz',
  'musunuz',
  'ki',
  'ne',
  'oldun',
  'oldunuz',
  'neler',
  'olabilir',
  'sorum',
  'soruyorum',
  'söyle',
];

$que = array_map(function($word) {
  return str_slug($word, ' ');
}, $que);
