<?php
define('__ROOT__', dirname(dirname(__FILE__))); 
require_once(__ROOT__.'/html/jsonRPCClient.php'); 
$btc = new jsonRPCClient('http://bitcoinrpc:FWzeNLbEU4gS2jZYk7WQ4pKVAYsZRnZkc3arpbi5gNS6@207.12.89.194:8332/');
$ltc = new jsonRPCClient('http://litecoinrpc:8hNh1BgXeyuptqkmZGJoqQ4MpfyePXa4sFRF5Yogjbgg@207.12.89.193:9332/');
$nmc = new jsonRPCClient('http://namecoinrpc:XcRwQrOsNEqOtfx439yBV353iVoclYrRlNXfiEtuGV59@207.12.89.193:8336/');
$ftc = new jsonRPCClient('http://feathercoinrpc:DFGsVT4QHr8WKENYCWzfS5rSM4Cv4tqDZS2MUENVLgHk@207.12.89.193:9337/');
?>