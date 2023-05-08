<?php

namespace App\NotaFiscal\Domain\Repository;

interface IHttpService
{
  function request($action, $data);
}
