var vm = new Vue({
    el:"#shopList",
    data:{},
    methods:{
        classify:function(){
            console.log(1)
            axios.get('/api/index/cates').then(function(res){
                console.log(res)
            })
        }
    },
    mounted:function(){
        this.classify()
    }
})