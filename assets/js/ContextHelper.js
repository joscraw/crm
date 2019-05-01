'use strict';

class ContextHelper {

    /**
     * Binds context to a function while preserving the toString(). When using toString() after binding
     * function { [native code] } is returned instead of an actual string representation of the function.
     * This function comes in handy because in the EventDispatcher singleSubscribe() we compare string representation
     * of functions so we don't add the same function twice.
     *
     * @see https://stackoverflow.com/questions/34255580/bind-that-does-not-return-native-code-in-javascript
     *
     * @param fun
     * @param ctx
     * @return {function(): *}
     */
    static bind(fun, ctx){
        let newFun = function(){
            return fun.apply(ctx, arguments);
        };
        newFun.toString = function(){
            return fun.toString();
        };
        return newFun;
    }

}

export default ContextHelper;