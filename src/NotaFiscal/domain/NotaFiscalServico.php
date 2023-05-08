<?php

namespace App\NotaFiscal\Domain;

class NotaFiscalServico
{
  private $params;

  public function __construct($data)
  {
    $this->params = $data;
  }

  public function values()
  {
  }
}
