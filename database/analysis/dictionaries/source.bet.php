<?php

$bet = [
  'bet',
  'bahis',
  'bets',
  'rulet',
  'bonus',
];

$bet = array_map(function($word) {
  return str_slug($word, ' ');
}, $bet);
