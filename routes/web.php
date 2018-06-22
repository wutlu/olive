<?php

Route::get('/', 'HomeController@index');

Route::get('kullanici/giris', 'UserController@login');
