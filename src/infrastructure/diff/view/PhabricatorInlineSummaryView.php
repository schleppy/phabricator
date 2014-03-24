<?php

final class PhabricatorInlineSummaryView extends AphrontView {

  private $groups = array();

  public function addCommentGroup($name, array $items) {
    if (!isset($this->groups[$name])) {
      $this->groups[$name] = $items;
    } else {
      $this->groups[$name] = array_merge($this->groups[$name], $items);
    }
    return $this;
  }

  public function render() {
    require_celerity_resource('inline-comment-summary-css');
    return hsprintf('%s%s', $this->renderHeader(), $this->renderTable());
  }

  private function renderHeader() {
    return phutil_tag_div('phabricator-inline-summary', pht('Inline Comments'));
  }

  private function renderTable() {
    $rows = array();
    foreach ($this->groups as $group => $items) {

      $has_where = false;
      foreach ($items as $item) {
        if (!empty($item['where'])) {
          $has_where = true;
          break;
        }
      }

      $rows[] = phutil_tag(
        'tr',
        array(),
        phutil_tag('th', array('colspan' => 3), $group));

      foreach ($items as $item) {
        $line = $item['line'];
        $length = $item['length'];
        if ($length) {
          $lines = $line."\xE2\x80\x93".($line + $length);
        } else {
          $lines = $line;
        }

        if (isset($item['href'])) {
          $href = $item['href'];
          $target = '_blank';
          $tail = " \xE2\x86\x97";
        } else {
          $href = '#inline-'.$item['id'];
          $target = null;
          $tail = null;
        }

        if ($href) {
          $lines = phutil_tag(
            'a',
            array(
              'href'    => $href,
              'target'  => $target,
              'class'   => 'num',
            ),
            array(
              $lines,
              $tail,
            ));
        }

        $where = idx($item, 'where');

        $colspan = ($has_where ? null : 2);
        $rows[] = phutil_tag(
          'tr',
          array(),
          array(
            phutil_tag('td', array('class' => 'inline-line-number'), $lines),
            ($has_where
              ? phutil_tag('td', array('class' => 'inline-which-diff'), $where)
              : null),
            phutil_tag(
              'td',
              array(
                'class' => 'inline-summary-content',
                'colspan' => $colspan,
              ),
              phutil_tag_div('phabricator-remarkup', $item['content']))));
      }
    }

    return phutil_tag(
      'table',
      array(
        'class' => 'phabricator-inline-summary-table',
      ),
      phutil_implode_html("\n", $rows));
  }

}
