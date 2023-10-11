<?php
namespace Core {

    /**
     * Class Renderer
     *
     * This class is responsible for rendering views with data.
     */
    class Renderer {

        /**
         * Render a view with data.
         *
         * @param string $view The path to the view file to be rendered.
         * @param array $data An associative array of data to be made available to the view.
         */
        public function render(string $view, array $data) {
            extract($data, EXTR_SKIP);
            require $view;
        }
	}
}