<?php namespace Bruha\Generator\Builder;
/**
 * Template building class
 * @author Radek Brůha
 * @version 1.0
 */
class Builder {
	private $parameters = [];
	private $settings;

	public function __construct(\stdClass $settings) {
		$this->settings = $settings;
		$this->parameters = array_merge((array)$settings, ['module' => $settings->module ? "\\{$settings->module}Module" : '']);
	}

	public function build() {
		foreach(\Nette\Utils\Finder::findFiles('*.latte')->from($this->settings->template) as $template) {
			$destination = $this->settings->netteRoot . str_replace($this->settings->template, '', mb_substr($template, 0, -6));
			if(mb_strpos($destination, '\Module') !== FALSE) $destination = str_replace('\Module', ($this->settings->module ? '\\' . $this->settings->module . 'Module' : '\\'), $destination);
			if(mb_strpos($destination, '\NDBT') !== FALSE && $this->settings->target !== \Utils\Constants::TARGET_NETTE_DATABASE ||
				mb_strpos($destination, '\D2') !== FALSE && $this->settings->target !== \Utils\Constants::TARGET_DOCTRINE2
			) {
				continue;
			} else $destination = str_replace(['\NDBT', '\D2'], '\\', $destination);
			if(mb_strpos($destination, '\\Table') !== FALSE) {
				foreach($this->settings->tables as $table) {
					$this->parameters['table'] = $table;
					$newDestination = str_replace('\\Table', "\\$table->sanitizedName", $destination);
					if(mb_strpos(basename($destination), '.') !== FALSE) {
						$this->saveTemplate($newDestination, $this->processTemplate($template, $this->parameters));
					} else $this->processTemplate($newDestination, $this->parameters);
				}
			} else {
				if(mb_strpos(basename($destination), '.') !== FALSE) {
					$this->saveTemplate($destination, $this->processTemplate($template, $this->parameters));
				} else $this->processTemplate($template, $this->parameters);
			}
		}
		foreach(\Nette\Utils\Finder::findFiles('*.*')->exclude('*.latte')->from($this->settings->template) as $template) {
			$destination = $this->settings->netteRoot . str_replace($this->settings->template, '', $template);
			\Nette\Utils\FileSystem::copy($template, $destination);
		}
	}

	private function processTemplate($template, $paremeters) {
		return str_replace(' l:', ' n:', (new \Latte\Engine)->renderToString($template, $paremeters));
	}

	private function saveTemplate($destination, $content) {
		return \Nette\Utils\FileSystem::write($destination, $content, 0777) ? $content : FALSE;
	}
}