<?php
namespace App\Service\Src\Node;

// Control word node: \par
class ParNode extends CtrlWordNode {

  public function __construct(int $param) {
    parent::__construct("\\par", $param);
  }

  public function text() {
    return "\n";
  }
}
