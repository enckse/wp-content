hphp
===

The "Happy Paws Haven Plugin" is used to provide dynamic value updates without haphazardly updating pages where WordPress, the theme, and other plugins can make it difficult to do basic development. Additionally plugins and/or workarounds to provide these features make the pages themselves polluted with components that really do not belong inlined in a page (e.g. javascript).

## settings

Once the plugin is installed (via zip), navigate to the administrative Settings area and go to "HPHP", this is where the settings are for the plugin.

### variables

When the plugin is first installed the only things to edit are in the "Advanced Configuration" area (click the button to expand). What needs to happen first is to setup the data information you want to have variables for. For Happy Paws Haven itself, that is something like this JSON:

```
{
  "counters": {
    "dogs_adopted": "int",
    "cats_adopted": "int",
    "pocket_pets_adopted": "int",
    "trap_neuter_return": "int",
    "sanctuary_animals": "int",
    "total_animals_adopted_matcher": "sum"
  },  
  "scripts": {
    "plugin": "js"
  }
}
```

Which creates 5 integer counters and a single sum (add up counters) variable. Save the settings and when the page reloads it will populate with the new variables.

From here:
- "int" counters are simple (unsigned) integer counters
- "sum" counters do a (primitive) string contains match and any other variable containing the text for the "sum" variable will be tallied up

Additionally it will deploy the core plugin javascript

### pages allowed

The value to control which pages are allowed to process the data (to minimize extra processing) is a comma-delimited list of (again primitive) string contains matching against page names. (additionally, regardless of the situation, the payload generated will include the page name if it can be determined).

For example if there is a "home" and "summary" page that should have the counters enabled (but no other pages), one would put "home,summary" into the field.

### onready

If using document ready (via jQuery) is preferred (not the default) check the corresponding box. This will initialize counter data using the normal document ready function.

## pages

The value of a counter will be placed in the name from the settings JSON with a prefix of `hphp_counter_` and suffix of `_display` (e.g. to set a div value from `dogs_adopted` one would place `<div id="hphp_counter_dogs_adopted_display"></div>`).

Some helper utility scripts are available in the [utils area](/enckse/wp-content/tree/master/plugins/hphp/utils) to provide starters for embedding the necessary HTML/CSS to enable the plugin to update components.

### manually

If the "onready" checkbox is NOT set, then a `<script>` tag needs to be added in the page and it needs to call one of `hphpInit()` or `hphpInitAndHide("id-to-hide")` where `hphpInitAndHide` will call the init function and then hide the given div (likely the segment holding the `<script>` component if this is used).
