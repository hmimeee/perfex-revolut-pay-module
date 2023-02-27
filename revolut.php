<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__.'/libraries/Revolut_gateway.php');
include_once(__DIR__.'/libraries/Revolut.php');
include_once(__DIR__.'/controllers/Revolut.php');

/*
Module Name: Revolut
Description: Module for the payment gateway of Revolut
Version: 1.0
Author: Hossain Mohammad Imran (hmime.com)
*/

register_payment_gateway('revolut_gateway', '[revolut]');