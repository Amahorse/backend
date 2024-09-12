<?php

/**
 * Classe speciale che può essere istanziata solo una volta in tutto il sistema ad ogni api call, 
 * funge da decorator api per i model, se non dichiarati e sovrascritti nei controller di ogni elemento
 * crea delle chiamate api standard ai modelli che a loro volta possono sovracrivere i metodi standard 
 * dell'interfaccia modello, questo automatizza tutto il processo di sviluppo API
 */

declare(strict_types=1);

namespace Kodelines\Abstract;

use Context\Cli\Console;

/**
 * Classe abstract perchè deve essere estensione dei controller che gli aggiungono questi metodi
 */

abstract class Command 
{

  /**
   * Contiene l'istanza console
   *
   * @var [type]
   */
  public Console $console;


  /**
   * La richiesta viene processata dal container e dalla app principale nel middleware che ritorna dati fixati
   * e nell'array $this->data per risparmiare tutte le volte di recuperarli nel metodo PSR7 con le varie funzioni
   *
   * @param Console $console
   */
  public function __construct(Console $console) {

    $this->console = $console;

  }





}
