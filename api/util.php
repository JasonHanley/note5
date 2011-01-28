<?php

function exception_handler($exception) {
  echo "Uncaught exception: " , $exception->getMessage(), "\n";
}
