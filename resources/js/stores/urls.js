import { defineStore } from "pinia";

export const useUrlsStore = defineStore('urls', {
    state: () => ({
        /**
         * Urls are created setUrls(),
         * defined from ../../../resources/config/shared-variables/main.php
         * Or other resource to view
         * These will not be visible in the development console
         */
    }),
    getters: {
        /**
         * User storeUrl.[name_url], Ex storeUrl.home, storeUrl.participants.list_users
         */
        //Get url in the first level
        getUrl: (state) => {
            return (name) => state[name];
        },
    },
    actions: {
        setUrls(objUrls){
            for(const key in objUrls)
            {
                this[key] = objUrls[key];
            }
        }
    }

});
