<?php

session_start();

$_SESSION['language'] = "ru";
header("Location: ../../?p=1");