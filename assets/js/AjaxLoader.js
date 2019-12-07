'use strict';

class AjaxLoader {
    static start($container) {
        $container.prepend(AjaxLoader.markup());
    }
    static kill($container) {
        $container.find('.pace').remove();
    }
    static markup() {
        return `
      	<div class="pace">
      	    <div class="pace-progress" data-progress-text="100%" data-progress="99" style="transform: translate3d(100%, 0px, 0px);">
		    <div class="pace-progress-inner"></div>
		    </div>
		    <div class="pace-activity"></div>
		</div>
    `;
    }
}

export default AjaxLoader;