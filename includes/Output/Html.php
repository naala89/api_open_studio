<?php
/**
 * Class Html.
 *
 * @package    ApiOpenStudio
 * @subpackage Output
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the ApiOpenStudio Public License.
 *             If a copy of the license was not distributed with this file,
 *             You can obtain one at https://www.apiopenstudio.com/license/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Output;

use ApiOpenStudio\Core\DataContainer;

/**
 * Class Html
 *
 * Outputs the results as HTML.
 */
class Html extends Xml
{
    /**
     * {@inheritDoc}
     *
     * @var string The string to contain the content type header value.
     */
    protected string $header = 'Content-Type:text/html';

    /**
     * {@inheritDoc}
     *
     * @var array Details of the processor.
     */
    protected array $details = [
        'name' => 'Html',
        'machineName' => 'html',
        'description' => 'Output in the results of the resource in HTML format to a remote server.',
        'menu' => 'Output',
        'input' => [
            'destination' => [
                'description' => 'Destination URLs for the output.',
                'cardinality' => [0, '*'],
                'literalAllowed' => true,
                'limitProcessors' => [],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => '',
            ],
            'method' => [
                'description' => 'HTTP delivery method when sending output. Only used in the output section.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitProcessors' => [],
                'limitTypes' => ['text'],
                'limitValues' => ['get', 'post', 'push', 'delete', 'put'],
                'default' => '',
            ],
            'options' => [
                // phpcs:ignore
                'description' => 'Extra Curl options to be applied when sent to the destination (e.g. cursor: -1, screen_name: foobarapi, skip_status: true, etc).',
                'cardinality' => [0, '*'],
                'literalAllowed' => true,
                'limitProcessors' => ['field'],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => '',
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     *
     * @return DataContainer Result of the processor.
     */
    public function process(): DataContainer
    {
        $this->logger->info('Output: ' . $this->details()['machineName']);
        return parent::process();
    }

    /**
     * {@inheritDoc}
     *
     * @param boolean $data Boolean data.
     *
     * @return string HTML string.
     */
    protected function fromBoolean(bool &$data): string
    {
        $prefix = '<!DOCTYPE html><html>';
        $prefix .= '<head><meta charset="utf-8"><title>HTML generated by Datagator</title></head><body>';
        $suffix = '</body></html>';
        return $prefix . $data ? 'true' : 'false' . $suffix;
    }

    /**
     * {@inheritDoc}
     *
     * @param integer $data Integer data.
     *
     * @return string HTML string.
     */
    protected function fromInteger(int &$data): string
    {
        $prefix = '<!DOCTYPE html><html>';
        $prefix .= '<head><meta charset="utf-8"><title>HTML generated by Datagator</title></head><body>';
        $suffix = '</body></html>';
        return $prefix . $data . $suffix;
    }

    /**
     * {@inheritDoc}
     *
     * @param float $data Float data.
     *
     * @return string HTML string.
     */
    protected function fromFloat(float &$data): string
    {
        $prefix = '<!DOCTYPE html><html>';
        $prefix .= '<head><meta charset="utf-8"><title>HTML generated by Datagator</title></head><body>';
        $suffix = '</body></html>';
        return $prefix . $data . $suffix;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $data XML data.
     *
     * @return string HTML string.
     */
    protected function fromXml(string &$data): string
    {
        $prefix = '<!DOCTYPE html><html>';
        $prefix .= '<head><meta charset="utf-8"><title>HTML generated by Datagator</title></head><body>';
        $suffix = '</body></html>';
        return $prefix . $data . $suffix;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $data HTML data.
     *
     * @return string HTML string.
     */
    protected function fromHtml(string &$data): string
    {
        return $data;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $data Text data.
     *
     * @return string HTML string.
     */
    protected function fromText(string &$data): string
    {
        $prefix = '<!DOCTYPE html><html>';
        $prefix .= '<head><meta charset="utf-8"><title>HTML generated by Datagator</title></head><body>';
        $suffix = '</body></html>';
        return $prefix . $data . $suffix;
    }

    /**
     * {@inheritDoc}
     *
     * @param array $data Array data.
     *
     * @return string HTML string.
     */
    protected function fromArray(array &$data): string
    {
        $prefix = '<!DOCTYPE html><html>';
        $prefix .= '<head><meta charset="utf-8"><title>HTML generated by Datagator</title></head><body>';
        $suffix = '</body></html>';
        return $prefix . $this->array2dl($data) . $suffix;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $data Json data.
     *
     * @return string HTML string.
     */
    protected function fromJson(string &$data): string
    {
        $prefix = '<!DOCTYPE html><html>';
        $prefix .= '<head><meta charset="utf-8"><title>HTML generated by Datagator</title></head><body>';
        $suffix = '</body></html>';
        return $prefix . $data . $suffix;
    }

    /**
    * Convert an array to a dl element.
     *
    * @param array $arr Array to convert.
     *
    * @return string Converted string.
    */
    private function array2dl(array &$arr): string
    {
        $result = '<dl>';
        foreach ($arr as $key => $val) {
            $result .= "<dt>$key</dt>";
            if (is_array($val)) {
                $result .= '<dd>' . $this->array2dl($val) . '</dd>';
            } else {
                $result .= "<dd>$val</dd>";
            }
        }
        return "$result</dl>";
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $data Image data.
     *
     * @return string HTML string.
     */
    protected function fromImage(&$data): string
    {
        $prefix = '<!DOCTYPE html><html>';
        $prefix .= '<head><meta charset="utf-8"><title>HTML generated by Datagator</title></head><body>';
        $suffix = '</body></html>';
        return $prefix . $data . $suffix;
    }
}
