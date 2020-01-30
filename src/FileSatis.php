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

class FileSatis
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $hash = '';

    /**
     * File constructor.
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;

        $this->load();
    }

    /**
     * File destructor
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Check if file exists
     * @return bool
     */
    public function isFile(): bool
    {
        return is_file($this->file);
    }

    /**
     * Check file is writable
     * @return bool
     */
    public function isWritable(): bool
    {
        return is_writable($this->file);
    }

    /**
     * @param string $url
     * @return bool
     */
    public function addRepository(string $url): bool
    {
        if (!isset($this->data['repositories'])) {
            $this->data['repositories'] = [];
        }

        foreach ($this->data['repositories'] as $val) {
            if ($url === $val['url']) {
                return false;
            }
        }

        $this->data['repositories'][] = [
            'type' => 'vcs',
            'url'  => $url
        ];

        return true;
    }

    /**
     * @return bool
     */
    private function hasDataChanged(): bool
    {
        return $this->hash !== $this->getHash();
    }

    /**
     * @return string
     */
    private function getHash(): string
    {
        return sha1(json_encode($this->data));
    }

    /**
     * Load data
     */
    private function load(): void
    {
        if (!$this->isFile()) {
            return;
        }

        if (!$this->data) {
            $this->data = json_decode(file_get_contents($this->file), true);
            $this->hash = $this->getHash();
        }
    }

    /**
     * Save data
     */
    private function save(): void
    {
        if (!$this->isFile()) {
            return;
        }

        if (!$this->isWritable()) {
            return;
        }

        if (! $this->hasDataChanged()) {
            return;
        }

        file_put_contents($this->file, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}