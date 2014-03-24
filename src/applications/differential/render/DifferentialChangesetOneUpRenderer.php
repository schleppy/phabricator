<?php

final class DifferentialChangesetOneUpRenderer
  extends DifferentialChangesetHTMLRenderer {

  public function isOneUpRenderer() {
    return true;
  }

  public function renderTextChange(
    $range_start,
    $range_len,
    $rows) {

    $primitives = $this->buildPrimitives($range_start, $range_len);

    $out = array();
    $left_char = $this->getOldAttachesToNewFile()
      ? 'N'
      : 'O';
    $right_char = $this->getNewAttachesToNewFile()
      ? 'N'
      : 'O';
    $left_id = $this->getOldChangesetID();
    $right_id = $this->getNewChangesetID();
    foreach ($primitives as $p) {
      $type = $p['type'];
      switch ($type) {
        case 'old':
        case 'new':
          $out[] = hsprintf('<tr>');
          if ($type == 'old') {
            if ($p['htype']) {
              $class = 'left old';
            } else {
              $class = 'left';
            }
#<<<<<<< HEAD
            $out[] = hsprintf('<th>%s</th>', $p['line']);
            $out[] = hsprintf('<th></th>');
            $out[] = hsprintf('<td style="width: 0px;min-width:0px;"></td>');
            $out[] = hsprintf('<td class="%s" style="width:100%%;">%s</td>', $class, $p['render']);
#=======
            #$out[] = phutil_tag('th', array(), $p['line']);
            #$out[] = phutil_tag('th', array());
            #$out[] = phutil_tag('td', array('class' => $class), $p['render']);
#>>>>>>> upstream/master
          } else if ($type == 'new') {
            if ($p['htype']) {
              $class = 'right new';
              $out[] = phutil_tag('th', array());
            } else {
              $class = 'right';
              $out[] = phutil_tag('th', array(), $p['oline']);
            }
#<<<<<<< HEAD
            $n_id = hsprintf('id="C%s%sL%s"', $right_id, $right_char, $p['line']);
            $out[] = hsprintf('<th %s>%s</th>', $n_id, $p['line']);
            $out[] = hsprintf('<td style="width:0px;min-width:0px;"></td>');
            $out[] = hsprintf('<td class="%s" style="width:100%%;">%s</td>', $class, $p['render']);
#=======
            #$out[] = phutil_tag('th', array(), $p['line']);
            #$out[] = phutil_tag('td', array('class' => $class), $p['render']);
#>>>>>>> upstream/master
          }
          $out[] = hsprintf('</tr>');
          break;
        case 'inline':
          $out[] = hsprintf('<tr><th /><th />');
          $out[] = hsprintf('<td style="width: 0px;min-width:0px;"></td>');
          $out[] = hsprintf('<td style="width:100%%;">');

          $inline = $this->buildInlineComment(
            $p['comment'],
            $p['right']);
          $inline->setBuildScaffolding(false);
          $out[] = $inline->render();

          $out[] = hsprintf('</td></tr>');
          break;
        default:
          $out[] = hsprintf('<tr><th /><th /><td style="width: 0px;min-width:0px;"></td><td style="width:100%%;">%s</td></tr>', $type);
          break;
      }
    }

    if ($out) {
      return $this->wrapChangeInTable(phutil_implode_html('', $out));
    }
    return null;
  }

  public function renderFileChange($old_file = null,
                                   $new_file = null,
                                   $id = 0,
                                   $vs = 0) {
    throw new Exception("Not implemented!");
  }

}
