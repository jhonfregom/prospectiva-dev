import { defineStore } from "pinia";
export const useTextsStore = defineStore('texts', {
    state: () => ({
        /**
         * Texts are created setTexts(),
         * defined from ../../../resources/config/shared-variables/main.php
         * Or other resource to view
         * These will not be visible in the development console
         */
        locale: null //Current language to translations
    }),
    getters: {
        /**
         * User storeText.[name_text], Ex storeText.home, storeText.participants.list_users
         */
        //Get text in the first level
        getText: (state) => {
            return (name) => state[name];
        },
        getLocale: (state) => {
            return state.locale
        }
    },
    actions: {
        setTexts(objTexts){
            for(const key in objTexts)
            {
                this[key] = objTexts[key];
            }
        },
        setLocale(locale){
            this.locale = locale;
        }
    }
});
