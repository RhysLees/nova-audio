<template>
	<PanelItem :index="index" :field="field">
		<template #value>
			<template v-if="shouldShowAudio">
				<audio
					controls
					:controlslist="controlsList"
					:autoplay="autoplay"
					:preload="preload"
					:src="field.previewUrl"
				/>
			</template>

			<template v-if="field.value && !field.previewUrl">
                <span class="break-words"> {{ field.value }} </span>
            </template>

			<span v-if="!field.value && !field.previewUrl">&mdash;</span>

			<p v-if="shouldShowToolbar" class="flex items-center text-sm mt-3">
				<a
					v-if="field.downloadable"
					:dusk="field.attribute + '-download-link'"
					@keydown.enter.prevent="download"
					@click.prevent="download"
					tabindex="0"
					class="
						cursor-pointer
						dim
						btn btn-link
						text-primary
						inline-flex
						items-center
					"
				>
					<icon
						class="mr-2"
						type="download"
						view-box="0 0 24 24"
						width="16"
						height="16"
					/>
					<span class="class mt-1">{{ __("Download") }}</span>
				</a>
			</p>
		</template>
	</PanelItem>
</template>

<script>
export default {
	props: ["resource", "resourceName", "resourceId", "field"],
	methods: {
		download() {
			const { resourceName, resourceId } = this;
			const attribute = this.field.attribute;
			let link = document.createElement("a");
			link.href = `/nova-api/${resourceName}/${resourceId}/download/${attribute}`;
			link.download = "download";
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		},
	},
	computed: {
		hasValue() {
			return (
				Boolean(this.field.value || this.previewUrl) &&
				!Boolean(this.missing)
			);
		},
		
		previewUrl() {
			return this.field.previewUrl;
		},
		
		shouldShowAudio() {
			return Boolean(this.field.previewUrl);
		},
		
		shouldShowToolbar() {
			return Boolean(this.field.downloadable && this.hasValue);
		},
		
		rounded() {
			return this.field.rounded;
		},

		maxWidth() {
			return this.field.maxWidth || 320;
		},
		
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
	},
};
</script>