<template>
	<DefaultField
		:field="currentField"
		:label-for="labelFor"
		:errors="errors"
		:full-width-content="true"
		:show-help-text="!isReadonly && showHelpText"
	>
		<template #field>
			<div v-if="hasValue" :class="{ 'mb-6': !currentlyIsReadonly }">
				<template v-if="field.value && !previewUrl">
					<card
						class="
							relative
							flex
							p-4
							overflow-hidden
							border
							item-center
							align-middle
							border-lg border-50
						"
					>
						<audio
							class="w-full mr-3"
							controls
							:controlslist="controlsList"
							:autoplay="autoplay"
							:preload="preload"
							:src="field.previewUrl"
						/>

						<DeleteButton
							:dusk="field.attribute + '-internal-delete-link'"
							class="ml-auto"
							v-if="shouldShowRemoveButton"
							@click="confirmRemoval"
						/>
					</card>
				</template>

				<p
					v-if="previewUrl && !currentlyIsReadonly"
					class="flex items-center mt-3 text-sm"
				>
					<DeleteButton
						:dusk="field.attribute + '-delete-link'"
						v-if="shouldShowRemoveButton"
						@click="confirmRemoval"
					>
						<span class="mt-1 ml-2 class">
							{{ __("Delete") }}
						</span>
					</DeleteButton>
				</p>

				<ConfirmUploadRemovalModal
					:show="removeModalOpen"
					@confirm="removeFile"
					@close="closeRemoveModal"
				/>
			</div>

			<p
				v-if="!hasValue && currentlyIsReadonly"
				class="pt-2 text-sm text-90"
			>
				{{ __("This file field is read-only.") }}
			</p>

			<span
				v-if="shouldShowField"
				class="mr-4 form-file"
				:class="{ 'opacity-75': currentlyIsReadonly }"
			>
				<input
					ref="fileField"
					:dusk="field.attribute"
					class="select-none form-file-input"
					type="file"
					:id="idAttr"
					name="name"
					@change="fileChange"
					:disabled="currentlyIsReadonly || uploading"
					:accept="field.acceptedTypes"
				/>
				<label
					:for="labelFor"
					class="
						inline-flex
						items-center
						flex-shrink-0
						px-3
						font-bold
						bg-white
						border-2
						rounded
						cursor-pointer
						focus:outline-none focus:ring
						border-primary-300
						dark:border-gray-500
						hover:border-primary-500
						active:border-primary-400
						dark:hover:border-gray-400
						dark:active:border-gray-300
						dark:bg-transparent
						text-primary-500
						dark:text-gray-400
						h-9
					"
				>
					<span v-if="uploading"
						>{{ __("Uploading") }} ({{ uploadProgress }}%)</span
					>
					<span v-else>{{ __("Choose File") }}</span>
				</label>
			</span>

			<span v-if="shouldShowField" class="text-sm select-none text-90">
				{{ currentLabel }}
			</span>

			<p v-if="hasError" class="mt-2 text-xs text-red-500">
				{{ firstError }}
			</p>
		</template>
	</DefaultField>
</template>

<script>
import {
	Errors,
	DependentFormField,
	HandlesValidationErrors,
} from "laravel-nova";

export default {
	emits: ["file-upload-started", "file-upload-finished", "file-deleted"],

	props: [
		"resourceId",
		"relatedResourceName",
		"relatedResourceId",
		"viaRelationship",
	],

	mixins: [HandlesValidationErrors, DependentFormField],

	data: () => ({
		file: null,
		fileName: "",
		removeModalOpen: false,
		missing: false,
		deleted: false,
		uploadErrors: new Errors(),
		vaporFile: {
			key: "",
			uuid: "",
			filename: "",
			extension: "",
		},
		uploading: false,
		uploadProgress: 0,
	}),

	mounted() {
		this.field.fill = (formData) => {
			let attribute = this.field.attribute;

			if (this.file && !this.isVaporField) {
				formData.append(attribute, this.file, this.fileName);
			}

			if (this.file && this.isVaporField) {
				formData.append(attribute, this.fileName);
				formData.append(
					"vaporFile[" + attribute + "][key]",
					this.vaporFile.key
				);
				formData.append(
					"vaporFile[" + attribute + "][uuid]",
					this.vaporFile.uuid
				);
				formData.append(
					"vaporFile[" + attribute + "][filename]",
					this.vaporFile.filename
				);
				formData.append(
					"vaporFile[" + attribute + "][extension]",
					this.vaporFile.extension
				);
			}
		};
	},

	methods: {
		/**
		 * Respond to the file change
		 */
		fileChange(event) {
			let path = event.target.value;
			let fileName = path.match(/[^\\/]*$/)[0];
			this.fileName = fileName;
			let extension = fileName.split(".").pop();
			this.file = this.$refs.fileField.files[0];

			if (this.isVaporField) {
				this.uploading = true;
				this.$emit("file-upload-started");

				Vapor.store(this.$refs.fileField.files[0], {
					progress: (progress) => {
						this.uploadProgress = Math.round(progress * 100);
					},
				}).then((response) => {
					this.vaporFile.key = response.key;
					this.vaporFile.uuid = response.uuid;
					this.vaporFile.filename = fileName;
					this.vaporFile.extension = extension;
					this.uploading = false;
					this.uploadProgress = 0;
					this.$emit("file-upload-finished");
				});
			}
		},

		/**
		 * Confirm removal of the linked file
		 */
		confirmRemoval() {
			this.removeModalOpen = true;
		},

		/**
		 * Close the upload removal modal
		 */
		closeRemoveModal() {
			this.removeModalOpen = false;
		},

		/**
		 * Remove the linked file from storage
		 */
		async removeFile() {
			this.uploadErrors = new Errors();

			const {
				resourceName,
				resourceId,
				relatedResourceName,
				relatedResourceId,
				viaRelationship,
			} = this;
			const attribute = this.field.attribute;

			const uri =
				this.viaRelationship &&
				this.relatedResourceName &&
				this.relatedResourceId
					? `/nova-api/${resourceName}/${resourceId}/${relatedResourceName}/${relatedResourceId}/field/${attribute}?viaRelationship=${viaRelationship}`
					: `/nova-api/${resourceName}/${resourceId}/field/${attribute}`;

			try {
				await Nova.request().delete(uri);
				this.closeRemoveModal();
				this.deleted = true;
				this.$emit("file-deleted");
				Nova.success(this.__("The file was deleted!"));
			} catch (error) {
				this.closeRemoveModal();

				if (error.response?.status == 422) {
					this.uploadErrors = new Errors(error.response.data.errors);
				}
			}
		},
	},

	computed: {
		/**
		 * Determine if the field has an upload error.
		 */
		hasError() {
			return this.uploadErrors.has(this.fieldAttribute);
		},

		/**
		 * Return the first error for the field.
		 */
		firstError() {
			if (this.hasError) {
				return this.uploadErrors.first(this.fieldAttribute);
			}
		},

		/**
		 * The current label of the file field.
		 */
		currentLabel() {
			return this.fileName || this.__("no file selected");
		},

		/**
		 * The ID attribute to use for the file field.
		 */
		idAttr() {
			return this.labelFor;
		},

		/**
		 * The label attribute to use for the file field.
		 */
		labelFor() {
			let name = this.resourceName;

			if (this.relatedResourceName) {
				name += "-" + this.relatedResourceName;
			}

			return `file-${name}-${this.field.attribute}`;
		},

		/**
		 * Determine whether the field has a value.
		 */
		hasValue() {
			return (
				Boolean(this.field.value || this.previewUrl) &&
				!Boolean(this.deleted) &&
				!Boolean(this.missing)
			);
		},

		/**
		 * Determine whether the field has an original name.
		 */
		hasOriginalName() {
			return Boolean(this.field.storeOriginalName);
		},

		/**
		 * Determine whether the field should show the loader component.
		 */
		shouldShowAudio() {
			return !Boolean(this.deleted) && Boolean(this.previewUrl);
		},

		/**
		 * Determine whether the file field input should be shown.
		 */
		shouldShowField() {
			return Boolean(!this.currentlyIsReadonly);
		},

		/**
		 * Determine whether the field should show the remove button.
		 */
		shouldShowRemoveButton() {
			return Boolean(
				this.currentField.deletable && !this.currentlyIsReadonly
			);
		},

		/**
		 * Determining if the field is a Vapor field.
		 */
		isVaporField() {
			return this.currentField.component == "vapor-file-field";
		},

		/**
		 * Deteming the fields controlList.
		 */
		controlsList() {
			let controlsList = "nodownload";

			if(! this.field.playbackRate){
				controlsList += ' noplaybackrate';
			}

			return controlsList;
		},

		/**
		 * Determine whether the field should preload the file.
		 */
		preload() {
			return this.field.preload || "none";
		},

		/**
		 * Determine whether the field should autoplay the audio.
		 */
		autoplay() {
			return this.field.autoplay || false;
		},
	},
};
</script>
