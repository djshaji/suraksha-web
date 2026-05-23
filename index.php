<?php
$title = "SPMR Suraksha";
$description = "Security Application for Govt. SPMR College";
$LOGIN_URI = $_SERVER['REQUEST_URI'] ?? '/';
include 'lib/header.php';
?>

<div class="container ">
  <div class="w-100 text-center">
    <a href="/"><img class="img-fluid abstract-brand" src="/logo-wide.png" alt=""></a>
  </div>
  <div class="col-md-6 mx-auto text-center">
    <h1 class="display-4 mt-4 mb-3">SPMR Suraksha</h1>
    <p class="lead mb-4">Security Application for Govt. SPMR College</p>
    <?php include "lib/login.php"; ?>
  </div>
</div>
