<?php

namespace Drupal\kozon\Ajax;

use Drupal\Component\Render\PlainTextOutput;

/**
 * Defines an AJAX command to open certain content in a dialog.
 *
 * @ingroup ajax
 */
class KozonAjaxCommand implements CommandInterface, CommandWithAttachedAssetsInterface {

  use CommandWithAttachedAssetsTrait;

  /**
   * The selector of the dialog.
   *
   * @var string
   */
  protected $selector;

  /**
   * The content for the dialog.
   *
   * Either a render array or an HTML string.
   *
   * @var string|array
   */
  protected $content;

  /**
   * Constructs an OpenDialogCommand object.
   *
   * @param string $selector
   *   The selector of the dialog.
   * @param string|array $content
   *   The content that will be placed in the dialog, either a render array
   *   or an HTML string.
   */
  public function __construct($selector, $content) {
    $this->selector = $selector;
    $this->content = $content;
  }

  /**
   * Implements \Drupal\Core\Ajax\CommandInterface:render().
   */
  public function render() {
    return [
      'command' => 'KozonAjax',
      'selector' => $this->selector,
      'data' => $this->getRenderedContent(),
    ];
  }

}
