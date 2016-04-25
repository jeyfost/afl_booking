<?php

session_start();

$_SESSION['language'] = "en";
header("Location: ../en/?p=1");