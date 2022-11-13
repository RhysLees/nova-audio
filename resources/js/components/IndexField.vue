<template>
	<div>
		<audio
			v-if="field.previewUrl"
			class="max-w-[250px]"
			controls
			:controlslist="controlsList"
			:autoplay="autoplay"
			:preload="preload"
			:src="field.previewUrl"
		/>
		<span v-else>&mdash;</span>
		<span>{{ controlslist }}</span>
	</div>
</template>

<script>
export default {
	props: ["viaResource", "viaResourceId", "resourceName", "field"],
	computed: {
		/**
		 * Deteming the fields controlList.
		 */
		controlsList() {
			let controlsList = 'nodownload';

			if(! this.field.playbackRate){
				controlsList += ' noplaybackrate';
			}
			
			return controlsList;
		},

		/**
		 * Determine whether the field should preload the file.
		 */
		preload() {
            return this.field.preload || 'none';
        },

		/**
		 * Determine whether the field should autoplay the audio.
		 */
        autoplay() {
            return this.field.autoplay || false;
        }
	}
};
</script>