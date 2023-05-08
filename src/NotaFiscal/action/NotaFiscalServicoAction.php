<?php

namespace App\NotaFiscal\Action;

use App\NotaFiscal\Infra\Http\HttpService;
use App\NotaFiscal\Infra\Repository\NotaFiscalServicoRepository;
use App\NotaFiscal\Infra\Soap\StructureSoap;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;

abstract class NotaFiscalServicoAction
{
  protected $repository;
  protected $soapStructure;
  protected $httpService;

  public function __construct()
  {
    $connection = ConnectionManager::get('default');
    $this->repository = new NotaFiscalServicoRepository($connection);

    $this->soapStructure = new StructureSoap();
    $this->httpService = new HttpService();
  }
}
