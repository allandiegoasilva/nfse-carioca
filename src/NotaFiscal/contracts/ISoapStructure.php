<?php

namespace App\NotaFiscal\Contracts;

interface ISoapStructure
{
  function emitir($data): array;
  function cancelar($data): array;
  function consultar($data): array;
}
