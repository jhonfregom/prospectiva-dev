import { defineStore } from "pinia";
export const useFieldsStore = defineStore('fields',{
    state: () => ({
        participant: new Object(),
    }),
    getters: {
        getField: (state) => {
            return (name) => state[name];
        }
    },
    actions: {
        /**
         * @param objFields Object
         */
        setFields(objFields){
            this.participant = objFields.participant;
        }
    }
});
