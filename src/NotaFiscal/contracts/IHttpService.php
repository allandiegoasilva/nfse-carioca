<?php

namespace App\NotaFiscal\Contracts;

interface IHttpService
{
  function request($action, $data);
}
