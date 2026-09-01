export const inputTypes = [
    "button",
    "checkbox",
    "color",
    "date",
    "datetime-local",
    "email",
    "file",
    "hidden",
    "image",
    "month",
    "number",
    "password",
    "radio",
    "range",
    "reset",
    "search",
    "tel",
    "text",
    "textarea",
    "time",
    "url",
    "week",
];

export const inputSchema = {
  sharedAttributes: [
    { attribute: "id", expectedType: "string" },
    { attribute: "class", expectedType: "string" },
    { attribute: "style", expectedType: "string" },
    { attribute: "disabled", expectedType: "boolean" },
    { attribute: "title", expectedType: "string" },
    { attribute: "lang", expectedType: "string" },
    { attribute: "dir", expectedType: "ltr|rtl|auto" },
    { attribute: "role", expectedType: "string" },
    { attribute: "tabindex", expectedType: "number" },
    { attribute: "accesskey", expectedType: "string" },
    { attribute: "contenteditable", expectedType: "boolean" },
    { attribute: "draggable", expectedType: "boolean" },
    { attribute: "translate", expectedType: "boolean" },
    { attribute: "data-*", expectedType: "string" },
  ],

  types: {
    button: [
      { attribute: "value", expectedType: "string" },
      { attribute: "popovertarget", expectedType: "string" },
      { attribute: "popovertargetaction", expectedType: "hide|show|toggle" }
    ],

    checkbox: [
      { attribute: "checked", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "label", expectedType: "string" }
    ],

    color: [
      { attribute: "alpha", expectedType: "boolean" },
      { attribute: "colorspace", expectedType: "limited-srgb|display-p3" }
    ],

    date: [
      { attribute: "list", expectedType: "string" },
      { attribute: "max", expectedType: "string" },
      { attribute: "min", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "step", expectedType: "number" },
    ],

    "datetime-local": [
      { attribute: "list", expectedType: "string" },
      { attribute: "max", expectedType: "string" },
      { attribute: "min", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "step", expectedType: "number" },
    ],

    email: [
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "list", expectedType: "string" },
      { attribute: "maxlength", expectedType: "number" },
      { attribute: "minlength", expectedType: "number" },
      { attribute: "multiple", expectedType: "boolean" },
      { attribute: "pattern", expectedType: "string" },
      { attribute: "placeholder", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "size", expectedType: "number" },
      { attribute: "dirname", expectedType: "string" }
    ],

    file: [
      { attribute: "accept", expectedType: "string" },
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "capture", expectedType: "boolean" },
      { attribute: "multiple", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" }
    ],

    hidden: [
      { attribute: "dirname", expectedType: "string" }
    ],

    image: [
      { attribute: "alt", expectedType: "string" },
      { attribute: "formaction", expectedType: "string" },
      { attribute: "formenctype", expectedType: "application/x-www-form-urlencoded|multipart/form-data|text/plain" },
      { attribute: "formmethod", expectedType: "get|post|dialog" },
      { attribute: "formnovalidate", expectedType: "boolean" },
      { attribute: "formtarget", expectedType: "string" },
      { attribute: "height", expectedType: "number" },
      { attribute: "src", expectedType: "string" },
      { attribute: "width", expectedType: "number" }
    ],

    month: [
      { attribute: "list", expectedType: "string" },
      { attribute: "max", expectedType: "string" },
      { attribute: "min", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "step", expectedType: "number" }
    ],

    number: [
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "list", expectedType: "string" },
      { attribute: "max", expectedType: "string" },
      { attribute: "min", expectedType: "string" },
      { attribute: "placeholder", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "step", expectedType: "number" }
    ],

    password: [
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "list", expectedType: "string" },
      { attribute: "maxlength", expectedType: "number" },
      { attribute: "minlength", expectedType: "number" },
      { attribute: "pattern", expectedType: "string" },
      { attribute: "placeholder", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "size", expectedType: "number" },
      { attribute: "dirname", expectedType: "string" }
    ],

    radio: [
      { attribute: "label", expectedType: "string" }
    ],

    range: [
      { attribute: "list", expectedType: "string" },
      { attribute: "max", expectedType: "string" },
      { attribute: "min", expectedType: "string" },
      { attribute: "step", expectedType: "number" }
    ],

    reset: [
      { attribute: "formaction", expectedType: "string" },
      { attribute: "formenctype", expectedType: "application/x-www-form-urlencoded|multipart/form-data|text/plain" },
      { attribute: "formmethod", expectedType: "get|post|dialog" },
      { attribute: "formnovalidate", expectedType: "boolean" },
      { attribute: "formtarget", expectedType: "string" },
    ],

    search: [
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "dirname", expectedType: "string" },
      { attribute: "list", expectedType: "string" },
      { attribute: "maxlength", expectedType: "number" },
      { attribute: "minlength", expectedType: "number" },
      { attribute: "pattern", expectedType: "string" },
      { attribute: "placeholder", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "size", expectedType: "number" },
    ],

    tel: [
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "dirname", expectedType: "string" },
      { attribute: "list", expectedType: "string" },
      { attribute: "maxlength", expectedType: "number" },
      { attribute: "minlength", expectedType: "number" },
      { attribute: "pattern", expectedType: "string" },
      { attribute: "placeholder", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "size", expectedType: "number" }
    ],

    text: [
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "dirname", expectedType: "string" },
      { attribute: "list", expectedType: "string" },
      { attribute: "maxlength", expectedType: "number" },
      { attribute: "minlength", expectedType: "number" },
      { attribute: "pattern", expectedType: "string" },
      { attribute: "placeholder", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "size", expectedType: "number" }
    ],

    textarea: [
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "cols", expectedType: "integer" },
      { attribute: "maxlength", expectedType: "number" },
      { attribute: "placeholder", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "rows", expectedType: "number" },
      { attribute: "wrap", expectedType: "hard|soft" }
    ],

    time: [
      { attribute: "list", expectedType: "string" },
      { attribute: "max", expectedType: "string" },
      { attribute: "min", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "step", expectedType: "number" }
    ],

    url: [
      { attribute: "autofocus", expectedType: "boolean" },
      { attribute: "dirname", expectedType: "string" },
      { attribute: "list", expectedType: "string" },
      { attribute: "maxlength", expectedType: "number" },
      { attribute: "minlength", expectedType: "number" },
      { attribute: "pattern", expectedType: "string" },
      { attribute: "placeholder", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "size", expectedType: "number" }
    ],

    week: [
      { attribute: "list", expectedType: "string" },
      { attribute: "max", expectedType: "string" },
      { attribute: "min", expectedType: "string" },
      { attribute: "readonly", expectedType: "boolean" },
      { attribute: "required", expectedType: "boolean" },
      { attribute: "step", expectedType: "number" }
    ]
  },

  ariaAttributes: [
    { attribute: "activedescendant", expectedType: "string" },
    { attribute: "atomic", expectedType: "boolean" },
    { attribute: "autocomplete", expectedType: "inline|list|both|none" },
    { attribute: "braillelabel", expectedType: "string" },
    { attribute: "brailleroledescription", expectedType: "string" },
    { attribute: "busy", expectedType: "boolean" },
    { attribute: "checked", expectedType: "boolean" },
    { attribute: "colcount", expectedType: "number" },
    { attribute: "colindex", expectedType: "number" },
    { attribute: "colindextext", expectedType: "string" },
    { attribute: "colspan", expectedType: "number" },
    { attribute: "controls", expectedType: "string" },
    { attribute: "current", expectedType: "boolean|page|step|location|date|time" },
    { attribute: "describedby", expectedType: "string" },
    { attribute: "description", expectedType: "string" },
    { attribute: "details", expectedType: "string" },
    { attribute: "disabled", expectedType: "boolean" },
    { attribute: "dropeffect", expectedType: "copy|move|link|execute|popup|none" },
    { attribute: "errormessage", expectedType: "string" },
    { attribute: "expanded", expectedType: "boolean" },
    { attribute: "flowto", expectedType: "string" },
    { attribute: "grabbed", expectedType: "boolean" },
    { attribute: "haspopup", expectedType: "boolean" },
    { attribute: "invalid", expectedType: "boolean" },
    { attribute: "keyshortcuts", expectedType: "string" },
    { attribute: "label", expectedType: "string" },
    { attribute: "labelledby", expectedType: "string" },
    { attribute: "level", expectedType: "number" },
    { attribute: "live", expectedType: "off|polite|assertive" },
    { attribute: "modal", expectedType: "boolean" },
    { attribute: "multiline", expectedType: "boolean" },
    { attribute: "multiselectable", expectedType: "boolean" },
    { attribute: "orientation", expectedType: "horizontal|vertical" },
    { attribute: "owns", expectedType: "string" },
    { attribute: "placeholder", expectedType: "string" },
    { attribute: "posinset", expectedType: "number" },
    { attribute: "pressed", expectedType: "boolean" },
    { attribute: "readonly", expectedType: "boolean" },
    { attribute: "relevant", expectedType: "additions|removals|text|all|additions text|additions removals|removals text|additions removals text" },
    { attribute: "required", expectedType: "boolean" },
    { attribute: "roledescription", expectedType: "string" },
    { attribute: "rowcount", expectedType: "number" },
    { attribute: "rowindex", expectedType: "number" },
    { attribute: "rowindextext", expectedType: "string" },
    { attribute: "rowspan", expectedType: "number" },
    { attribute: "selected", expectedType: "boolean" },
    { attribute: "setsize", expectedType: "number" },
    { attribute: "sort", expectedType: "ascending|descending|none|other" },
    { attribute: "valuemax", expectedType: "number" },
    { attribute: "valuemin", expectedType: "number" },
    { attribute: "valuenow", expectedType: "number" },
    { attribute: "valuetext", expectedType: "string" }
  ]
};