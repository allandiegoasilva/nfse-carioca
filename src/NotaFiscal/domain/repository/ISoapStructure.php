<?php

namespace App\NotaFiscal\Domain\Repository;

interface ISoapStructure
{
  function emitir($data): array;
  function cancelar($data): array;
  function consultar($data): array;
}
