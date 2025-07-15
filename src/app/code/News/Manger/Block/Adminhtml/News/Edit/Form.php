<?php

namespace News\Manger\Block\Adminhtml\News\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
  /**
   * Prepare form
   *
   * @return $this
   */
  protected function _prepareForm()
  {
    /** @var \News\Manger\Model\News $model */
    $model = $this->_coreRegistry->registry('news_news');

    /** @var \Magento\Framework\Data\Form $form */
    $form = $this->_formFactory->create([
      'data' => [
        'id' => 'edit_form',
        'action' => $this->getData('action'),
        'method' => 'post'
      ]
    ]);

    $form->setHtmlIdPrefix('news_');

    $fieldset = $form->addFieldset(
      'base_fieldset',
      ['legend' => __('General Information'), 'class' => 'fieldset-wide']
    );

    if ($model->getId()) {
      $fieldset->addField('news_id', 'hidden', ['name' => 'news_id']);
    }

    $fieldset->addField(
      'news_title',
      'text',
      [
        'name' => 'news_title',
        'label' => __('Title'),
        'title' => __('Title'),
        'required' => true
      ]
    );

    $fieldset->addField(
      'news_content',
      'textarea',
      [
        'name' => 'news_content',
        'label' => __('Content'),
        'title' => __('Content'),
        'required' => true
      ]
    );

    $fieldset->addField(
      'news_status',
      'select',
      [
        'name' => 'news_status',
        'label' => __('Status'),
        'title' => __('Status'),
        'required' => true,
        'options' => [
          1 => __('Active'),
          0 => __('Inactive')
        ]
      ]
    );
    $fieldset->addField(
      'created_at',
      'date',
      [
        'name' => 'created_at',
        'label' => __('Created At'),
        'title' => __('Created At'),
        'required' => false,
        'disabled' => true,
        'format' => 'yyyy-MM-dd', // تحديد الصيغة
        'input_format' => 'yyyy-MM-dd',
        'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'), // صورة أيقونة التاريخ
      ]
    );

    $fieldset->addField(
      'updated_at',
      'date',
      [
        'name' => 'updated_at',
        'label' => __('Updated At'),
        'title' => __('Updated At'),
        'required' => false,
        'disabled' => true,
        'format' => 'yyyy-MM-dd',
        'input_format' => 'yyyy-MM-dd',
        'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
      ]
    );


    $form->setValues($model->getData());
    $form->setUseContainer(true);
    $this->setForm($form);

    return parent::_prepareForm();
  }
}
