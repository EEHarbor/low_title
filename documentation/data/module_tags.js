window.doc_page = {
    addon: 'Low Title',
    title: 'Tags',
    sections: [
        {
            title: '',
            type: 'tagtoc',
            desc: 'Low Title has the following front-end tags: ',
        },
        {
            title: '',
            type: 'tags',
            desc: ''
        },
    ],
    tags: [

        {
            tag: '{exp:low_title:entry}',
            shortname: 'exp_',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'entry_id',
                            desc: 'Entry id of the title to fetch.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:entry entry_id="15"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'url_title',
                            desc: '	URL title for the title to fetch.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:entry url_title="{segment_2}" channel="default_site"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'channel_id',
                            desc: '	The id of the channel you want to limit the query to. Use weblog_id in EE1.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:entry channel_id="1"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'channel',
                            desc: '	The short name of the channel you want to limit the query to. Use weblog in EE1.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:entry url_title="{segment_2}" channel="default_site"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'custom_field',
                            desc: '',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:entry url_title="{segment_3}" custom_field="title_{language}"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'fallback',
                            desc: '	Use in combination with custom_field. Set to yes to fall back to the entry title if the custom field is empty. (v2.1.0+)',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:entry fallback="yes"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'show_error',
                            desc: '	Shows an error if custom field was not found. Only in combination with the custom_field parameter, defaults to “no”.',
                            type: '',
                            accepts: '',
                            default: 'no',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:entry show_error="yes"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },
        {
            tag: '{exp:low_title:category}',
            shortname: 'exp_',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'category_id',
                            desc: 'Category id of the title to fetch.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:category category_id="18"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'url_title',
                            desc: '	Category URL title for the title to fetch.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:category url_title="{segment_4}" category_group="1"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'category_group',
                            desc: '	The id of the category group you want to limit the query to.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:category url_title="{segment_4}" category_group="1"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'custom_field',
                            desc: '	Short name of the custom category field to return instead of the category name.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:category url_title="{segment_3}" custom_field="title_{language}"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'fallback',
                            desc: '	Use in combination with custom_field. Set to yes to fall back to the category title if the custom field is empty.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:category fallback="yes"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'show_error',
                            desc: '	Shows an error if custom field was not found. Only in combination with the custom_field parameter, defaults to no.',
                            type: '',
                            accepts: '',
                            default: 'no',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:category show_error="yes"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },
        {
            tag: '{exp:low_title:channel}',
            shortname: 'exp_',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'channel_id',
                            desc: '	Channel id of the title to fetch. Use weblog_id in EE1.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:channel weblog_id="3"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'channel_name',
                            desc: 'Short name of the channel for the title to fetch. Use weblog_name in EE1.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:channel channel_name="{segment_1}"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },
        {
            tag: '{exp:low_title:site}',
            shortname: 'exp_',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'site_id	',
                            desc: '	Site id of the title to fetch.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:site site_id="1"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'site_name',
                            desc: '	Short name of the site for the title to fetch.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_title:site site_name="{segment_1}"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                      
                    ]
                }
            ]
        },



    ]
};