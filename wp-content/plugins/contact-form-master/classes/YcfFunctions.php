<?php
class YcfFunctions {

	public static function createSelectBox($data, $selectedValue, $attrs) {

		$attrString = '';
		$selected = '';

		if(!empty($attrs) && isset($attrs)) {

			foreach ($attrs as $attrName => $attrValue) {
				$attrString .= ''.$attrName.'="'.$attrValue.'" ';
			}
		}

		$selectBox = '<select '.$attrString.'>';

		foreach ($data as $value => $label) {

			/*When is multiselect*/
			if(is_array($selectedValue)) {
				$isSelected = in_array($value, $selectedValue);
				if($isSelected) {
					$selected = 'selected';
				}
			}
			else if($selectedValue == $value) {
				$selected = 'selected';
			}
			else if(is_array($value) && in_array($selectedValue, $value)) {
				$selected = 'selected';
			}

			$selectBox .= '<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
			$selected = '';
		}

		$selectBox .= '</select>';

		return $selectBox;
	}

	public static function createAdminViewHtml($formElement, $args) {
		ob_start();
		$elementId = $formElement['id'];
		$orderId = $args['oderId'];
		?>
		<div class="ycf-element-info-wrapper">
			<div class="ycf-view-element-wrapper" data-options="false" id="<?php echo $elementId ?>">
				<div class="ycf-element-label-wrapper">
					<span class="sub-option-hidden-data"  data-order="<?php echo $orderId; ?>"></span>
					<span><?php echo $formElement['label'] ?></span>
				</div>
				<div class="ycf-element-conf-wrapper">
					<span class="ycf-conf-element ycf-conf-home"></span>
					<span class="ycf-conf-element ycf-delete-element ycf-hide-element" data-id="<?php echo $elementId ?>"></span>
				</div>
			</div>
			<?php
				if($formElement['type'] == 'select') {
					echo self::adminElementOptions($formElement, $args);
				}
				else {
					echo self::simpleElementOptions($formElement, $args);
				}
			?>
			<div class="ycf-element-margin-bottom"></div>
		</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public static function adminElementOptions($formElement, $args) {
		$elementId = $formElement['id'];
		ob_start();
		?>
		<div class="ycf-element-options-wrapper ycf-hide-element" style="width: 200%;">
			<div class="ycf-sub-option-wrapper">
				<span class="element-option-sub-label">Label</span>
				<input type="text" class="element-label ycf-element-sub-option"  value="<?php echo $formElement['label'];?>" data-key="label" data-id="<?php echo $elementId;?>">
			</div>
			<div class="ycf-sub-option-wrapper">
				<span class="element-option-sub-label">Name</span>
				<input type="text" class="element-name ycf-element-sub-option" value="<?php echo $formElement['name']; ?>" data-key="name" data-id="<?php echo $elementId;?>">
			</div>
			<div class="ycf-sub-option-wrapper ycf-sub-options-header">
				<div class="row margin-bottom-fix">
					<div class="col-md-4">
						<span>Select Options</span>
					</div>
					<div class="col-md-7">
						<input type="button" value="Add option" class="ycf-add-sub-option-group btn btn-primary js-disable-in-ajax" data-id="<?php echo $elementId; ?>" data-type="option">
					</div>
				</div>
				
				<?php
					echo self::optionsValuesHtml($formElement);
				?>
			</div>
		</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public static function simpleElementOptions($formElement, $args) {

		$elementId = $formElement['id'];
		ob_start();
		?>
		<div class="ycf-element-options-wrapper ycf-hide-element">
			<div class="ycf-sub-option-wrapper">
				<span class="element-option-sub-label">Label</span>
				<input type="text" class="element-label ycf-element-sub-option"  value="<?php echo $formElement['label'];?>" data-key="label" data-id="<?php echo $elementId;?>">
			</div>
			<div class="ycf-sub-option-wrapper">
				<span class="element-option-sub-label">Name</span>
				<input type="text" class="element-name ycf-element-sub-option" value="<?php echo $formElement['name']; ?>" data-key="name" data-id="<?php echo $elementId;?>">
			</div>
		</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public static function changeFieldsOrdering($fieldsData, $ordersId) {

		if(!empty($ordersId) && gettype($ordersId) == 'string') {
			$ordersId = explode(',', $ordersId);
		}

		if(!is_array($ordersId)) {
			return $fieldsData;
		}
		$newOrderingData = array();

		foreach($ordersId as $fieldId) {

			$currentFieldData = $fieldsData[$fieldId];
			if(!isset($currentFieldData)) {
				continue;
			}
			$newOrderingData[] = $currentFieldData;
		}

		if(empty($newOrderingData)) {
			return $fieldsData;
		}

		return $newOrderingData;
	}

	/*
	 * Options name value string
	 *
	 * @since 1.0.5
	 *
	 * @param array $fields
	 *
	 * @return string $optionsString
	 *
	 */
	public static function optionsValuesHtml($formElement) {

		$optionsString = '';

		if(empty($formElement)) {
			return $optionsString;
		}

		$elementId = $formElement['id'];
		$formElementOptions = $formElement['options'];
		$formElementOptions= json_decode(stripslashes($formElementOptions), true);

		if(empty($formElementOptions)) {
			return $optionsString;
		}
		$fieldOptions = $formElementOptions['fieldsOptions'];
		$fieldOptions = json_decode(stripslashes($fieldOptions), true);

		if(empty($fieldOptions)) {
			$optionsString .= '<div class="ycf-options-data-names">';
			$optionsString .= '</div>';
			return $optionsString;
		}

		$fieldsOrder = json_decode($formElementOptions['fieldsOrder'], true);

		$optionsString .= '<div class="ycf-options-data-names">';

		foreach($fieldsOrder as $fieldId) {

			$field = $fieldOptions[$fieldId];
			$label = $field['label'];
			$value = $field['value'];

			$optionsStringValues = self::subOptionsGroupOptions($fieldId, $elementId, $value, $label);
			$optionsString .= $optionsStringValues;
		}
		$optionsString .= '</div>';
		return $optionsString;
	}

	public static function subOptionsGroupOptions($fieldId, $elementId, $value, $label)
	{
		ob_start();
		?>
			<div class="row current-options data-type-sub-options sub-options-group-wrapper margin-bottom-fix" data-order="<?php echo $fieldId; ?>" data-id="<?php echo $elementId; ?>" data-type="option">
				<div class="col-md-4">
					<input type="text" class="sub-option-name form-control" name="value" value="<?php echo $value; ?>" style="margin-right: 5px;">
				</div>
				<div class="col-md-4">
					<input type="text" class="sub-option-value form-control" name="label" value="<?php echo $label; ?>" data-id="<?php echo $elementId; ?>" data-type="option">
				</div>
				<div class="col-md-2">
					<span class="delete-sub-option"></span>
				</div>
			</div>
		<?php
		$optionsStringValues = ob_get_contents();
		ob_end_clean();

		return $optionsStringValues;
	}
}

