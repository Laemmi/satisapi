<?php
/**
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @category   laemmi/satisapi
 * @author     Michael Lämmlein <laemmi@spacerabbit.de>
 * @copyright  ©2020 laemmi
 * @license    http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version    1.0.0
 * @since      2020-01-30
 */
declare(strict_types=1);

namespace Laemmi\Satisapi;

class App
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var FileSatis
     */
    private $file;

    /**
     * @return static
     */
    public static function run(): self
    {
        $config = require 'config/config.php';

        return new self(
            $config,
            new Request(),
            new Response(),
            new FileSatis($config['satisfile']));
    }

    /**
     * App constructor.
     * @param array $config
     * @param Request $request
     * @param Response $response
     * @param FileSatis $file
     */
    public function __construct(array $config, Request $request, Response $response, FileSatis $file)
    {
        $this->config   = $config;
        $this->request  = $request;
        $this->response = $response;
        $this->file     = $file;
    }

    /**
     * Dispatch
     */
    public function dispatch()
    {
        if (!$this->file->isFile()) {
            $this->terminate('Satis file not exists');
        }

        if (!$this->file->isWritable()) {
            $this->terminate('Satis file not writable');
        }

        if ($this->request->getHeader('X-Gitlab-Token') !== $this->config['gitlabtoken']) {
            $this->terminate('Access denied', 403);
        }

        if (!$this->request->isJson()) {
            $this->terminate('Only Json', 400);
        }

        $requstdata = $this->request->getBodyData();

        if (!isset($requstdata['project']['git_ssh_url'])) {
            $this->terminate('No project url found', 400);
        }

        $bol = $this->file->addRepository($requstdata['project']['git_ssh_url']);

        if ($bol) {
            $this->terminate('Add repository');
        }

        $this->terminate('Already added');
    }

    /**
     * @param string $msg
     * @param int $code
     */
    private function terminate(string $msg, int $code = 200)
    {
        $this->response
            ->setCode($code)
            ->setContentType('application/json')
            ->setBody(json_encode([
                'status' => $code,
                'message' => $msg
            ]))
            ->send();
    }
}