<?php

declare(strict_types=1);

namespace Kodelines\Interfaces;

interface ModelInterface
{

   public function query(array $filters = []):string;

   public function get(int $id, $filters = []):array|false ;

   public function slug(string $slug):array|false ;
  
   public function list(array $filters = []): array ;

   public function fullList(array $filters = []): array ;

   public function fullGet(int $id, $filters = []): array|false ;

   public function where(string $param, mixed $value): mixed; 

   public function create(array $values = [], $reget = true):array|false ;

   public function update(int $id, array $values = []):array|false ;

   public function delete(int $id): bool ;
 
}

?>