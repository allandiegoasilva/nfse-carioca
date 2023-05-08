<?php

namespace App\NotaFiscal\Domain\Repository;

use NotaFiscalServico;

interface INotaFiscalServicoRepository
{
  function create($notaFiscalServico): void;
  function read($id): array;
  function readAll(): array;
  function updateStatus($id, $status): void;
}
