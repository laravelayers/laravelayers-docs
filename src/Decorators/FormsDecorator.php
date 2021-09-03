<?php

namespace Laravelayers\Docs\Decorators;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Laravelayers\Form\Decorators\Form;
use Laravelayers\Form\Decorators\FormDecorator;
use Laravelayers\Form\Decorators\FormElementDecorator;
use Laravelayers\Foundation\Decorators\DataDecorator;
use Laravelayers\Navigation\Decorators\MenuDecorator;

class FormsDecorator extends DataDecorator
{
    use Form {
        Form::getElements as getElementsFromTrait;
    }

    /**
     * Content of the file "Forms".
     *
     * @var string
     */
    protected static $content = '';

    /**
     * Decorator startup method.
     *
     * @param $data
     * @return $this|static|mixed
     */
    public static function make($data = null)
    {
        if (is_string($data)) {
            static::$content = $data;

            $data = null;
        }

        $data = parent::make($data);

        foreach($data->initElements() as $key => $value) {
            $data->put($key, '');
        }

        return $data;
    }

    /**
     * Get the contents of the "Forms" file.
     *
     * @return string
     */
    public function getContent()
    {
        if (strpos(static::$content, '<!--form') !== false) {
            if (Request::get('checklist', Request::old('checklist'))) {
                return (string) $this->getElements()->setWarning('Checklist');
            }

            $elements = $this->prepareElements($this->initElements());

            foreach($elements as $element)
            {
                $form = FormDecorator::make([
                    $element//->addGroup(null)
                ])->setForm($elements->getForm()->addId('')->addName($elements->getForm()->get('name') . '_' . $element->get('name')));

                static::$content = str_replace('<!--form:' . $element->get('name') . '-->', $form, static::$content);
            }

            static::$content = str_replace('<!--form:text_grouping-->', $this->getElementsGroup(), static::$content);

            static::$content = str_replace('<!--form:text_line-->', $this->getElementsLine(), static::$content);

            static::$content = str_replace('<!--form:form_js-->',  $this->getElementsExample(), static::$content);

            static::$content = str_replace('<!--form:checklist-link-->',  $this->getChecklistLink(), static::$content);

            static::$content = str_replace('<!--form:checklist-link-start-->',  '<a href="?checklist=1">', static::$content);

            static::$content = str_replace('<!--form:checklist-link-end-->',  '</a>', static::$content);
        }

        return static::$content;
    }

    /**
     * Get the form elements.
     *
     * @return \Laravelayers\Form\Decorators\FormDecorator
     */
    public function getElements()
    {
        $elements = $this->getElementsFromTrait();

        $elements->text_group
            ->addLine('text');

        $elements->radio_group
            ->addGroup('')
            ->addLine('select')
            ->addLabel('Radio');

        return $elements;
    }

    /**
     * Get the form elements example.
     *
     * @return \Laravelayers\Form\Decorators\FormDecorator
     */
    public function getElementsExample()
    {
        return app(FormDecorator::class, [
            $this->initElementsExample(),
        ])->getElements($this);
    }

    /**
     * Get the form elements group.
     *
     * @return \Laravelayers\Form\Decorators\FormDecorator
     */
    public function getElementsGroup()
    {
        return app(FormDecorator::class, [
            $this->initElementsGroup(),
        ])->getElements($this);
    }

    /**
     * Get the form elements line.
     *
     * @return \Laravelayers\Form\Decorators\FormDecorator
     */
    public function getElementsLine()
    {
        return app(FormDecorator::class, [
            $this->initElementsLine(),
        ])->getElements($this);
    }

    /**
     * Get the form textarea images.
     *
     * @return \Laravelayers\Form\Decorators\FormElementDecorator
     */
    public function getElementTextareaImages()
    {
        return FormElementDecorator::make(array_merge($this->initElementTextarea()['textarea_js_images'], [
            'name' => 'textarea_js_images',
            'hidden' => false
        ]))->getELement();
    }

    /**
     * Get the checklist link.
     *
     * @return \Laravelayers\Form\Decorators\FormElementDecorator
     */
    protected function getChecklistLink()
    {
        return FormElementDecorator::make(array_merge($this->initElementButton()['button_link'], [
            'name' => 'checklist_link',
            'value' => [[
                'type' => 'select',
                'text' => 'Checklist',
                'link' => url()->current() . '?' . 'checklist=1',
                'class' => 'hollow expanded'
            ]]
        ]))->getElement();
    }

    /**
     * Initialize form elements.
     *
     * @return array
     */
    protected function initElements()
    {
        return array_merge(
            $this->initElementForm(),

            $this->initElementHidden(),

            $this->initElementDatetime(),
            $this->initElementDate(),
            $this->initElementTime(),
            $this->initElementMonth(),
            $this->initElementWeek(),

            $this->initElementEmail(),
            $this->initElementPassword(),
            $this->initElementTel(),
            $this->initElementUrl(),
            $this->initElementText(),

            $this->initElementSelect(),
            $this->initElementSearch(),
            $this->initElementCheckbox(),
            $this->initElementRadio(),
            $this->initElementNumber(),
            $this->initElementColor(),

            $this->initElementFile(),

            $this->initElementTextarea(),

            $this->initElementButton()
        );
    }

    /**
     * Initialize the form button.
     *
     * @return array
     */
    protected function initElementButton()
    {
        return [
            'button_dropdown' => [
                'type' => 'button.dropdown',
                'text' => 'Actions',
                'value' => [
                    'edit' => [
                        'value' => 'edit',
                        'group' => 1
                    ],
                    'back' => [
                        'link' => url()->current(),
                        'group' => 2
                    ],
                    'delete' => [
                        'value' => 'delete',
                        'group' => 1
                    ],
                ]
            ],
            'button_link' => [
                'type' => 'button',
                'value' => [[
                    'type' => 'back',
                    'link' => url()->current()
                ]],
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],

            'button' => [
                'type' => 'button',
                'class' => 'callout',
                'value' => [
                    'submit' => [
                        'type' => 'submit',
                        'class' => 'expanded'
                    ]
                ],
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'buttons' => [
                'type' => 'button',
                'value' => [
                    'back' => [
                        'link' => url()->current()
                    ],
                    'add' => [], 'cancel' => [], 'copy' => [], 'create' => [], 'reset' => [],
                    'edit' => [], 'sort' => [],
                    'external' => [],
                    'link' => [], 'next' => [], 'select' => [], 'show' => [],  'submit' => [],
                    'delete' => [],
                ]
            ],
            'button_text' => [
                'type' => 'button',
                'value' => [
                    'submit' => [
                        'text' => 'Text'
                    ]
                ],
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'button_icon' => [
                'type' => 'button',
                'value' => [[
                    'type' => 'submit',
                    'icon' => 'icon-paper-plane',
                    'reverse' => true,
                    'class' => 'warning'
                ]],
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'button_group' => [
                'type' => 'button.group',
                'value' => [
                    'edit' => 'Edit message',
                    'delete' => 'Delete message',
                ],
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
        ];
    }

    /**
     * Initialize the form checkbox.
     *
     * @return array
     */
    protected function initElementCheckbox()
    {
        return [
            'checkbox' => [
                'type' => 'checkbox',
                'value' => [
                    1 => [
                        'value' => 1,
                        'text' => 'One'
                    ],
                    2 => [
                        'value' => 2,
                        'text' => 'Two',
                        'class' => 'stat'
                    ]
                ],
                'class' => 'callout',
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'checkbox_group' => [
                'type' => 'checkbox.group',
                'value' => [
                    1 => 'One',
                    2 => 'Two'
                ],
                'group' => 'Checkbox',
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'checkbox_readonly' => [
                'type' => 'checkbox.readonly',
                'value' => 1,
                'checked' => 1,
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'checkbox_tree' => [
                'type' => 'checkbox.tree',
                'value' => MenuDecorator::make([
                    0 => [
                        'id' => 1,
                        'name' => 'Category 1',
                        'url' => '#category1',
                        'sorting' => 1,
                        'parent_id' => 0,
                    ],
                    1 => [
                        'id' => 2,
                        'name' => 'Category 2',
                        'url' => '#category2',
                        'sorting' => 2,
                        'parent_id' => 1,
                    ],
                    2 => [
                        'id' => 3,
                        'name' => 'Category 3',
                        'url' => '#category3',
                        'sorting' => 3,
                        'parent_id' => 2,
                    ],
                ])->getMenu()->getTree(),
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ]
        ];
    }

    /**
     * Initialize the form color.
     *
     * @return array
     */
    protected function initElementColor()
    {
        return [
            'color' => [
                'type' => 'color.group',
                'value' => '#cc4b37',
                'line' => 'select',
                'label' => 'Color'
            ],
        ];
    }

    /**
     * Initialize the form date.
     *
     * @return array
     */
    protected function initElementDate()
    {
        return [
            'date_js' => [
                'type' => 'date.js',
                'value' => Carbon::now(),
                'line' => 'datetime',
                'label' => 'Date',
                'data-alt-format' => 'Y-m-d',
            ],
        ];
    }

    /**
     * Initialize the form datetime.
     *
     * @return array
     */
    protected function initElementDatetime()
    {
        return [
            'datetime_js' => [
                'type' => 'datetime.js',
                'value' => Carbon::now(),
                'data-alt-format' => 'Y-m-d H:i:s',
                'line' => 'datetime',
                'label' => 'Datetime'
            ],
        ];
    }

    /**
     * Initialize the form email.
     *
     * @return array
     */
    protected function initElementEmail()
    {
        return [
            'email_js' => [
                'type' => 'email.js',
                'value' => '',
                'placeholder' => 'Email',
                'line' => 'text',
                'label' => 'Email',
                'help' => trans('validation.required', ['attribute' => 'Email']),
                'required' => '',
                'rules' => 'required|email'
            ],
        ];
    }

    /**
     * Initialize the form file.
     *
     * @return array
     */
    protected function initElementFile()
    {
        return [
            'file_js' => [
                'type' => 'file.js',
                'value' => [
                   0 => route('laravelayers.docs.images.show', [request()->route('lang'), 'logomark.min.svg']),
                ],
                'text' => 'Upload file',
                'data-image-mode' => 3,
                'accept' => [
                    'image/jpeg',
                    'image/png',
                    'application/pdf'
                ],
                'rules' => 'mimes:jpg,png,pdf',
                'group' => 'File'
            ],
        ];
    }

    /**
     * Initialize the form.
     *
     * @return array
     */
    protected function initElementForm()
    {
        return [
            'form_js' => [
                'type' => 'form.js',
                'method' => 'POST',
                'action' => url()->current() . '?' . 'checklist=1',
            ],
        ];
    }

    /**
     * Initialize the form hidden.
     *
     * @return array
     */
    public function initElementHidden()
    {
        return [
            'checklist' => [
                'type' => 'hidden',
                'value' => 1
            ]
        ];
    }

    /**
     * Initialize the form month.
     *
     * @return array
     */
    protected function initElementMonth()
    {
        return [
            'month_js' => [
                'type' => 'month.js',
                'value' => Carbon::now(),
                'line' => 'datetime',
                'label' => 'Month'
            ],
        ];
    }

    /**
     * Initialize the form number.
     *
     * @return array
     */
    protected function initElementNumber()
    {
        return [
            'number_js' => [
                'type' => 'number.js',
                'value' => 1,
                'data-validator-options' => htmlspecialchars('{"min": 0, "max": 99}'),
                'line' => 'select',
                'label' => 'Number'
            ],
        ];
    }

    /**
     * Initialize the form password.
     *
     * @return array
     */
    protected function initElementPassword()
    {
        return [
            'password' => [
                'type' => 'password.group',
                'value' => '123456',
                'placeholder' => 'Password',
                'line' => 'text',
                'label' => 'Password',
                'tooltip' => trans('validation.required', ['attribute' => 'Password']),
                'required' => '',
                'rules' => 'required'
            ],
        ];
    }

    /**
     * Initialize the form radio.
     *
     * @return array
     */
    protected function initElementRadio()
    {
        return [
            'radio_group' => [
                'type' => 'radio.group',
                'value' => [
                    1 => 'One',
                    2 => [
                        'value' => 2,
                        'text' => 'Two',
                        'checked' => true,
                    ]
                ],
                'group' => 'radio'
            ]
        ];
    }

    /**
     * Initialize the form search.
     *
     * @return array
     */
    protected function initElementSearch()
    {
        return [
            'search_js' => [
                'type' => 'search.js',
                'value' => '',
                'placeholder' => htmlspecialchars('Enter text like "search"'),
                'text' => 'icon-search',
                'data-ajax-url' => route('laravelayers.search.docs', [
                    session('locale', Request::route('lang'))
                ]),
                'line' => 'select',
                'label' => 'Search'
            ],
        ];
    }

    /**
     * Initialize the form select.
     *
     * @return array
     */
    protected function initElementSelect()
    {
        return [
            'select_js' => [
                'type' => 'select.js',
                'value' => MenuDecorator::make([
                    0 => [
                        'id' => 1,
                        'name' => 'Item 1',
                        'url' => '/menu/1',
                        'sorting' => 1,
                        'parent_id' => 0,
                    ],
                    1 => [
                        'id' => 2,
                        'name' => 'Item 2',
                        'url' => '/menu/2',
                        'sorting' => 2,
                        'parent_id' => 1,
                    ],
                    2 => [
                        'id' => 3,
                        'name' => 'Item 3',
                        'url' => '/menu/3',
                        'sorting' => 3,
                        'parent_id' => 2,
                    ],
                    3 => [
                        'id' => 4,
                        'name' => 'Item 4',
                        'url' => '/menu/4',
                        'sorting' => 4,
                        'parent_id' => 1,
                    ],
                    4 => [
                        'id' => 5,
                        'name' => 'Item 5',
                        'url' => '/menu/5',
                        'sorting' => 5,
                        'parent_id' => 0,
                    ]
                ])->getMenu()->setSelectedItems(3)->getTree(),
                'placeholder' => 'Items',
                'multiple' => true,
                'data-link-to-create' => url()->current() . '#type-select-js',
                'line' => 'select',
                'label' => 'Select'
            ],
        ];
    }

    /**
     * Initialize the form tel.
     *
     * @return array
     */
    protected function initElementTel()
    {
        return [
            'phone_js' => [
                'type' => 'tel.js',
                'value' => '+1 213 373 4253',
                'placeholder' => 'Phone',
                'data-validator-options' => "'isNotTollFreePhone': false, 'country': 'US'",
                'line' => 'text',
                'label' => 'Tel',
                'error' => 'The phone number must be in US format.',
                'rules' => 'phone:US',
            ],
        ];
    }

    /**
     * Initialize the form text.
     *
     * @return array
     */
    protected function initElementText()
    {
        return [
            'text_group' => [
                'type' => 'text.group',
                'value' => '',
                'label' => 'Name',
                'line' => 'text',
                'icon' => 'icon-user',
                'reverse' => true,
                'rules' => 'required'
            ],
            'text_label' => [
                'type' => 'text',
                'value' => '',
                'label' => 'Label',
                'line' => 'text',
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'text_help' => [
                'type' => 'text',
                'value' => '',
                'label' => 'Help',
                'help' =>  trans('validation.required', ['attribute' => 'Help']),
                'line' => 'text',
                'required' => '',
                'rules' => 'required',
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'text_tooltip' => [
                'type' => 'text',
                'value' => '',
                'label' => 'Tooltip',
                'tooltip' => trans('validation.required', ['attribute' => 'Tooltip']),
                'line' => 'text',
                'required' => '',
                'rules' => 'required',
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
            'text_error' => [
                'type' => 'text',
                'value' => '',
                'label' => 'Error',
                'error' => trans('validation.required', ['attribute' => 'Error']),
                'line' => 'text',
                'required' => '',
                'rules' => 'required',
                'hidden' => Request::get('checklist', Request::old('checklist'))
            ],
        ];
    }

    /**
     * Initialize the form textarea.
     *
     * @return array
     */
    protected function initElementTextarea()
    {
        return [
            'textarea_js' => [
                'type' => 'textarea.js',
                'value' => '**Markdown**',
                'data-editor-toolbar' => 'medium',
                'data-editor-gallery' => true,
                'label' => 'Markdown',
                'line' => 'textarea',
                'data-editor-height' => '94',
                'error' => trans('validation.required', ['attribute' => 'Markdown']),
                'required' => '',
                'rules' => 'required',
            ],
            'textarea_js_images' => [
                'type' => 'file.js',
                'accept' => [
                    'image/jpeg',
                    'image/png',
                    'image/svg+xml'
                ],
                'value' => [
                    0 => route('laravelayers.docs.images.show', [request()->route('lang'), 'logomark.min.svg']),
                    1 => route('laravelayers.docs.images.show', [request()->route('lang'), 'logotype.min.svg']),
                ],
                'data-ajax-url' => url()->current(),
                'multiple' => true,
                'hidden' => true
            ],
            'textarea_js_html' => [
                'type' => 'textarea.js',
                'value' => '<strong>Html</strong>',
                'data-editor-toolbar' => 'medium',
                'data-editor-type' => 'html',
                'data-editor-gallery' => false,
                'label' => 'HTML',
                'line' => 'textarea',
                'data-editor-height' => '100',
                'error' => trans('validation.required', ['attribute' => 'HTML']),
                'required' => '',
                'rules' => 'required'
            ],
        ];
    }

    /**
     * Initialize the form time.
     *
     * @return array
     */
    protected function initElementTime()
    {
        return [
            'time_js' => [
                'type' => 'time.js',
                'value' => Carbon::now(),
                'line' => 'datetime',
                'label' => 'Time'
            ],
        ];
    }

    /**
     * Initialize the form url.
     *
     * @return array
     */
    protected function initElementUrl()
    {
        return [
            'url_js' => [
                'type' => 'url.js',
                'value' => '',
                'placeholder' => 'test.localhost',
                'data-validator-options' => htmlspecialchars(json_encode([
                    'protocols' => ["https","http"]
                ])),
                'line' => 'text',
                'label' => 'Url'
            ]
        ];
    }

    /**
     * Initialize the form week.
     *
     * @return array
     */
    protected function initElementWeek()
    {
        return [
            'week_js' => [
                'type' => 'week.js',
                'value' => Carbon::now(),
                'line' => 'datetime',
                'label' => 'Week'
            ],
        ];
    }

    /**
     * Initialize form elements example.
     *
     * @return array
     */
    public function initElementsExample()
    {
        return [
            'example_form_js' => array_merge($this->initElementForm()['form_js'], [
                'name' => 'example_form_js'
            ]),
            'text_help' => array_merge($this->initElementText()['text_help'], [
                'id' => 'example_text_help',
                'label' => null,
                'placeholder' => 'Help',
                'line' => 'form.js',
                'group' => 'form.js'
            ]),
            'text_error' => array_merge($this->initElementText()['text_error'], [
                'id' => 'example_text_error',
                'label' => null,
                'placeholder' => 'Error',
                'line' => 'form.js',
                'group' => 'form.js'
            ]),
            'button' => array_merge($this->initElementButton()['button'], [
                'id' => 'example_button',
                'line' => 'form.js',
                'group' => 'form.js',
                'class' => null
            ])
        ];
    }

    /**
     * Initialize form elements to display in a group.
     *
     * @return array
     */
    public function initElementsGroup()
    {
        return [
            'group_form_js' => array_merge($this->initElementForm()['form_js'], [
                'name' => 'group_form_js'
            ]),
            'text_help' => array_merge($this->initElementText()['text_label'], [
                'id' => 'group_email',
                'label' => 'First Name',
                'group' => 'Name',
                'line' => null,
                'required' => ''
            ]),
            'text_tooltip' => array_merge($this->initElementText()['text_label'], [
                'id' => 'group_password',
                'label' => 'Second Name',
                'group' => 'Name',
                'line' => null,
                'required' => ''
            ]),
        ];
    }

    /**
     * Initialize form elements to display in a line.
     *
     * @return array
     */
    public function initElementsLine()
    {
        return [
            'line_form_js' => array_merge($this->initElementForm()['form_js'], [
                'name' => 'line_form_js'
            ]),
            'text_help' => array_merge($this->initElementText()['text_label'], [
                'id' => 'line_first_name',
                'label' => 'First Name',
                'required' => ''
            ]),
            'text_tooltip' => array_merge($this->initElementText()['text_label'], [
                'id' => 'line_second_name',
                'label' => 'Second Name',
                'required' => ''
            ]),
        ];
    }
}
