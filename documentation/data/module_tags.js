window.doc_page = {
    "addon": "Low Title",
    "title": "Tag Reference",
    "desc": "<p>These single tags return one label; they are not tag pairs. Supply an explicit identifier for the intended entry, category, channel, or site. All supplied lookup filters are combined. Each query takes its first matching row without a specified order; no matching row returns an empty string.</p><p>Entry lookups do not apply Channel Entries status, publication-date, expiry-date, or automatic site filters. Use explicit identifiers and select content intended for publication. See <a href=\"installation\">Installation</a> for package setup.</p>",
    "terms": [],
    "toc_intro": "Choose the label to retrieve:",
    "sections": [
        {
            "type": "tagtoc"
        },
        {
            "type": "tags"
        }
    ],
    "tags": [
        {
            "tag": "{exp:low_title:entry}",
            "shortname": "exp_",
            "desc": "Retrieve an entry title or custom field value.",
            "sections": [
                {
                    "type": "examples",
                    "title": "Usage",
                    "items": [
                        {
                            "item": "Single tag",
                            "desc": "Use an entry ID or URL title to identify the entry. The result is the entry’s title unless `custom_field` is supplied.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:entry entry_id=\"15\"}"
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "entry_id",
                            "desc": "The entry ID whose title to return.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:entry entry_id=\"15\"}"
                                }
                            ],
                            "type": "integer"
                        },
                        {
                            "item": "url_title",
                            "desc": "An exact entry URL title. Add a channel filter when URL titles may be shared by multiple channels.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:entry url_title=\"{segment_2}\" channel=\"news\"}"
                                }
                            ],
                            "type": "string"
                        },
                        {
                            "item": "channel_id",
                            "desc": "Restrict the lookup to a channel ID.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:entry url_title=\"{segment_2}\" channel_id=\"3\"}"
                                }
                            ],
                            "type": "integer"
                        },
                        {
                            "item": "channel",
                            "desc": "Restrict the lookup to a channel short name. This is a channel name, not a site short name.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:entry url_title=\"{segment_2}\" channel=\"news\"}"
                                }
                            ],
                            "type": "string"
                        },
                        {
                            "item": "custom_field",
                            "desc": "Return the named entry custom field instead of the title. This release reads entry custom fields only from legacy shared storage. Fields in separate per-field tables can trigger a database error; use Channel Entries tags to retrieve those fields. The custom-field examples assume a legacy-stored field.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:entry entry_id=\"15\" custom_field=\"legacy_alternate_title\"}"
                                }
                            ],
                            "type": "string"
                        },
                        {
                            "item": "fallback",
                            "desc": "With a legacy-stored `custom_field`, set `yes` to return the entry title when the selected custom field is an empty string. This does not supply fallback text when no entry matches.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:entry entry_id=\"15\" custom_field=\"legacy_alternate_title\" fallback=\"yes\"}"
                                }
                            ],
                            "type": "string",
                            "accepts": "yes or no",
                            "default": "no"
                        },
                        {
                            "item": "show_error",
                            "desc": "Return an explanatory string if the named custom field cannot be found. With the default behavior, an unknown custom field uses the regular entry title. This parameter does not report an ordinary unmatched entry ID or URL title. It checks the field name, not its storage layout, and does not prevent unsupported-storage database errors.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:entry entry_id=\"15\" custom_field=\"legacy_alternate_title\" show_error=\"yes\"}"
                                }
                            ],
                            "type": "string",
                            "accepts": "yes or no",
                            "default": "no"
                        }
                    ]
                }
            ]
        },
        {
            "tag": "{exp:low_title:category}",
            "shortname": "exp_low_title_category",
            "desc": "Retrieve a category name or custom category field.",
            "sections": [
                {
                    "type": "examples",
                    "title": "Usage",
                    "items": [
                        {
                            "item": "Single tag",
                            "desc": "Use a category ID or URL title, optionally restricted to a category group.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:category category_id=\"18\"}"
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "category_id",
                            "desc": "The category ID. The documented `C` prefix form is accepted too.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:category category_id=\"18\"}\n{exp:low_title:category category_id=\"C24\"}"
                                }
                            ],
                            "type": "string"
                        },
                        {
                            "item": "url_title",
                            "desc": "An exact category URL title. Add the category group when necessary to identify the intended category.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:category url_title=\"{segment_4}\" category_group=\"1\"}"
                                }
                            ],
                            "type": "string"
                        },
                        {
                            "item": "category_group",
                            "desc": "Restrict the lookup to this category group ID.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:category url_title=\"{segment_4}\" category_group=\"1\"}"
                                }
                            ],
                            "type": "integer"
                        },
                        {
                            "item": "custom_field",
                            "desc": "Return the named custom category field instead of the category name. This release reads category custom fields only from legacy shared storage. Fields in separate per-field tables can trigger a database error. The custom-field examples assume a legacy-stored field.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:category category_id=\"18\" custom_field=\"legacy_alternate_name\"}"
                                }
                            ],
                            "type": "string"
                        },
                        {
                            "item": "fallback",
                            "desc": "With a legacy-stored `custom_field`, set `yes` to return the category name when that field is an empty string. It does not supply output when no category matches.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:category category_id=\"18\" custom_field=\"legacy_alternate_name\" fallback=\"yes\"}"
                                }
                            ],
                            "type": "string",
                            "accepts": "yes or no",
                            "default": "no"
                        },
                        {
                            "item": "show_error",
                            "desc": "Return an explanatory string if the named custom category field does not exist. Without this option, an unknown custom field uses the category name. An unmatched category ID or URL title returns an empty string. It checks the field name, not its storage layout, and does not prevent unsupported-storage database errors.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:category category_id=\"18\" custom_field=\"legacy_alternate_name\" show_error=\"yes\"}"
                                }
                            ],
                            "type": "string",
                            "accepts": "yes or no",
                            "default": "no"
                        }
                    ]
                }
            ]
        },
        {
            "tag": "{exp:low_title:channel}",
            "shortname": "exp_low_title_channel",
            "desc": "Retrieve the display title of a channel.",
            "sections": [
                {
                    "type": "examples",
                    "title": "Usage",
                    "items": [
                        {
                            "item": "Single tag",
                            "desc": "Use a channel ID or short name.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:channel channel_id=\"3\"}"
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "channel_id",
                            "desc": "The channel ID whose display title to return.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:channel channel_id=\"3\"}"
                                }
                            ],
                            "type": "integer"
                        },
                        {
                            "item": "channel_name",
                            "desc": "The channel short name whose display title to return.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:channel channel_name=\"news\"}"
                                }
                            ],
                            "type": "string"
                        }
                    ]
                }
            ]
        },
        {
            "tag": "{exp:low_title:site}",
            "shortname": "exp_low_title_site",
            "desc": "Retrieve a site label.",
            "sections": [
                {
                    "type": "examples",
                    "title": "Usage",
                    "items": [
                        {
                            "item": "Single tag",
                            "desc": "Use a site ID or short name. The query does not automatically choose the current site.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:site site_id=\"1\"}"
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "params",
                    "title": "Parameters",
                    "items": [
                        {
                            "item": "site_id",
                            "desc": "The site ID whose label to return.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:site site_id=\"1\"}"
                                }
                            ],
                            "type": "integer"
                        },
                        {
                            "item": "site_name",
                            "desc": "The site short name whose label to return.",
                            "examples": [
                                {
                                    "tag_example": "{exp:low_title:site site_name=\"default_site\"}"
                                }
                            ],
                            "type": "string"
                        }
                    ]
                }
            ]
        }
    ]
};
