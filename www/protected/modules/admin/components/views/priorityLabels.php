<?php
foreach ($sources as $source) {
    echo '<p>'.$source.': '.CHtml::textField($inputName.'['.$source.']', (isset($values[$source]) ? $values[$source] : '')).'</p>';
}
?>