<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:65:"/data/wwwroot/aaa/public/../application/api/view/index/index.html";i:1537418387;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <title>艺陆纵横</title>
    <script src="/home/js/one.js"></script>
    <link rel="stylesheet" type="text/css" href="/home/css/style.css">
    <link rel="stylesheet" type="text/css" href="/home/css/swipeslider.css">
    <link rel="stylesheet" href="/home/css/index.css">
    <link rel="stylesheet" href="/home/css/swiper.min.css"> 
    <style>
        .swiper-wrapper{
            margin-top: 50px;
        }
        .swiper-container {
            width: 100%;
            height: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .swiper-slide {
            text-align: center;
            font-size: 18px;
            background: #fff;

            /* Center slide text vertically */
            display: -webkit-box;
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            -webkit-justify-content: center;
            justify-content: center;
            -webkit-box-align: center;
            -ms-flex-align: center;
            -webkit-align-items: center;
            align-items: center;
        }
    </style>
</head>
<body>
<div id="shopList" v-cloak>
    <div class="header">
        <ul class="IndexNav">
            <li>  <!-- classify -->
                <span @click = "feileFun">分类</span>
                <img src="/home/img/bot.png" alt="">
                <ol class="IndexNavTwo IndexNavTwo1" :class="feilei ? ' IndexNavTwoTrue' : ''">
                    <li :class="claAll ? 'checked' : ''" @click="goodsList">全部</li>
                    <li v-for="(item,index) in classifyList" @click="category(item,index)" :class="Number(item.fal) ? 'checked' : ''">{{item.cname}}</li>
                </ol>
            </li>
            <li>
                <span @click="stateFun">状态</span>
                <img src="/home/img/bot.png" alt="">
                <ol class="IndexNavTwo IndexNavTwoLeft" :class="stateBool ? ' IndexNavTwoTrue' : ''">
                    <li v-for="stateItem in stateCont" :class="stateItem.stateChecked ? 'checked' : ''" @click="screenState(stateItem)">{{stateItem.stateValue}}</li>
                </ol>
            </li>
            <li>
                <span @click="sourceFun">来源</span>
                <img src="/home/img/bot.png" alt="">
                <ol class="IndexNavTwo IndexNavTwoLeft2" :class="sourceBool ? ' IndexNavTwoTrue' : ''">
                    <li v-for="sourceItem in sourceCont" :class="sourceItem.sourceChecked ? 'checked' : ''" @click="screenSource(sourceItem)">{{sourceItem.sourceValue}}</li>
                </ol>
            </li>
            <li>
                <span @click="sortFun">排序</span>
                <img src="/home/img/bot.png" alt="">
                <ol class="IndexNavTwo sorting" :class="sortBool ? ' IndexNavTwoTrue' : ''">
                    <li v-for="sortItem in sortCont" :class="sortItem.sortChecked ? 'checked' : ''" @click="screenSort(sortItem)">{{sortItem.sortValue}}</li>
                </ol>
            </li>
        </ul>
        <!--<div class="jq22-container">-->
            <!--<article class="container">-->
                <!--<section>-->
                    <!--<figure id="full_feature" class="swipslider">-->
                        <!--<ul class="sw-slides">-->
                            <!--<li class="sw-slide" v-for="(item,index) in goodsListnames">-->
                                <!--<img :src="item.src" alt="Summer beach concept">-->
                            <!--</li>-->
                        <!--</ul>-->
                    <!--</figure>-->
                <!--</section>-->
            <!--</article>-->
        <!--</div>-->
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide" v-for="item in goodsListnames"><img :src="item.src" alt=""></div>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>

        </div>



        </div>
    <ul class="shopList">
        <li v-for="(item,index) in goodsListname">
            <a @click="location.href = 'ongoing.html?id='+item.id+'&g_id='+item.id">
                <img :src="item.cover_plan" alt="">
                <div  v-cloak>
                    <p class="shopName">{{item.goods_name}}</p>
                    <!--<p class="source">{{item.classname}}</p>-->
                    <p class="start_price">起拍价：<span>￥{{item.starting_price}}</span></p>
                    <p class="end_price">当前价：<span>￥{{item.maxprice}}</span></p>
                    <p class="endTime">距离结束：<span>{{end_time[index]}}</span></p>
                    <p class="endTime" v-show="false">距离开始：<span>{{end_time[index]}}</span></p>
                </div>
            </a>
        </li>
    </ul>
</div>
<ul class="footer">
    <li class="dangqian"><a href="index.html"><img src="/home/img/footerIndex.png" alt=""><span>首页</span></a></li>
    <li><a href="my.html"><img src="/home/img/me.png" alt=""><span>我的</span></a></li>
</ul>

<script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>
<script src="/home/js/vue.js"></script>
<script src="/home/js/swipeslider.min.js"></script>
<script src="/home/js/swiper.min.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    //轮播图
    $(window).load(function() {
        $('#full_feature').swipeslider();
        $('#content_slider').swipeslider({
            transitionDuration: 600,
            autoPlayTimeout: 10000,
            sliderHeight: '300px'
        });
        $('#responsiveness').swipeslider();
        $('#customizability').swipeslider({
            transitionDuration: 1500,
            autoPlayTimeout: 4000,
            timingFunction: 'cubic-bezier(0.38, 0.96, 0.7, 0.07)',
            sliderHeight: '30%'});
    });
    function timeFn(d1) {
        var dateBegin = new Date(d1.replace(/-/g, "/"));//将-转化为/，使用new Date
        var dateEnd = new Date();//获取当前时间
        var dateDiff = dateEnd.getTime() - dateBegin.getTime();//时间差的毫秒数
        var dayDiff = Math.floor(dateDiff / (24 * 3600 * 1000));//计算出相差天数
        var leave1=dateDiff%(24*3600*1000);    //计算天数后剩余的毫秒数
        var hours=Math.floor(leave1/(3600*1000))+1;//计算出小时数
        //计算相差分钟数
        var leave2=leave1%(3600*1000);    //计算小时数后剩余的毫秒数
        var minutes=Math.floor(leave2/(60*1000));//计算相差分钟数
        //计算相差秒数
        var leave3=leave2%(60*1000);      //计算分钟数后剩余的毫秒数
        var seconds=Math.round(leave3/1000);
        return Math.abs(dayDiff+1) + '天' + Math.abs(hours) + '小时' + Math.abs(minutes) + '分';
    }
    var vm = new Vue({
        el:"#shopList",
        data:{
            feilei:false,
            stateBool:false,
            stateCont:[{
                stateValue:"全部",
                stateChecked:true,
                id:""
            },{
                stateValue:"即将开始",
                stateChecked:false,
                id:1
            },{
                stateValue:"即将截拍",
                stateChecked:false,
                id:2
            }
            ],
            sourceBool:false,
            sourceCont:[{
                sourceValue:'全部',
                sourceChecked :true,
                id:''
            },{
                sourceValue:'艺术家',
                sourceChecked :false,
                id:1
            },{
                sourceValue:'收藏家',
                sourceChecked :false,
                id:2
            }],
            sortBool:false,
            sortCont:[{
                sortValue:'全部',
                sortChecked:true,
                id:''
            },{
                sortValue:'价格从低到高',
                sortChecked:false,
                id:1
            },{
                sortValue:'价格从高到低',
                sortChecked:false,
                id:2
            }],
            claAll:true,
            classifyList:'',
            banImg:'',
            goodsListname:'',
            goodsListnames:[
                // {src:"/uploads/20180907/ba982607f021970794cdf4fa39ea2be3.jpg"},
                // {src:"/uploads/20180907/b6f0c2d1ba8f93b755315db0853e8277.jpg"}
            ],
            end_time:[]     //距离结束时间
        },
        methods:{
            feileFun:function(num){
                this.feilei=!this.feilei;
                this.stateBool=false;
                this.sortBool=false;
                this.sourceBool=false
            },
            stateFun:function(){
                this.feilei=false;
                this.stateBool=!this.stateBool;
                this.sortBool=false;
                this.sourceBool=false
            },
            sourceFun:function(){
                this.feilei=false;
                this.stateBool=false;
                this.sortBool=false;
                this.sourceBool=!this.sourceBool
            },
            sortFun:function(){
                this.feilei=false;
                this.stateBool=false;
                this.sortBool=!this.sortBool;
                this.sourceBool=false
            },
            // 分类
            classify:function(){
                var _this = this;
                axios.get('/api/index/cates').then(function(res){
                    _this.classifyList = res.data.data;
                }).catch(function(){
                    console.log('我就知道')
                })
            },
            //点击筛选分类
            category:function(item,index){
                var _this = this;
                this.claAll = false;
                for(var i = 0;i < _this.classifyList.length;i++){
                    _this.classifyList[i].fal = 0;
                }
                item.fal = 1;
                var laiyuan = '', //来源
                    paixu = '',//排序
                    zhuangtai='';
                for(var a = 0;a < this.sourceCont.length;a++){
                    if(this.sourceCont[a].sourceChecked == true){
                        laiyuan = this.sourceCont[a].id;
                    }
                }
                for(var c = 0;c < this.sortCont.length;c++){
                    if(this.sortCont[c].sortChecked == true){
                        paixu = this.sortCont[c].id;
                    }
                }
                for(var b = 0;b < this.stateCont.length;b++){
                    if(this.stateCont[b].stateChecked == true){
                        zhuangtai = this.stateCont[b].id;
                    }
                }
                axios.get("/api/index/goodslist?page=1&cates=" + item.id + '&price='+ paixu +'&source='+ laiyuan +'&readytime=' + zhuangtai).then(function(res){
                    _this.goodsListname = res.data.data; 
                });
                this.feilei = false;
            },
            //点击筛选状态
            screenState:function(item){
                for(var b = 0;b < this.stateCont.length;b++){
                    this.stateCont[b].stateChecked = false;
                }
                item.stateChecked = true;
                var classi='',//分类
                    laiyuan = '', //来源
                    paixu = '',//排序
                    _this = this;
                for(var i = 0;i < this.classifyList.length;i++){
                    if(this.classifyList[i].fal == 1){
                        classi = this.classifyList[i].id;
                    }
                }
                for(var a = 0;a < this.sourceCont.length;a++){
                    if(this.sourceCont[a].sourceChecked == true){
                        laiyuan = this.sourceCont[a].id;
                    }
                }
                for(var c = 0;c < this.sortCont.length;c++){
                    if(this.sortCont[c].sortChecked == true){
                        paixu = this.sortCont[c].id;
                    }
                }
                axios.get('http://www.yiluzongheng.com/api/index/goodslist/?page=1&cates=' + classi + '&price='+ paixu +'&source='+ laiyuan +'&readytime=' + item.id).then(function(res){
                    _this.goodsListname = res.data.data;
                    setInterval(function(){
                        _this.end_time = [];
                        for(var i = 0;i<_this.goodsListname.length;i++){
                            _this.end_time.push(timeFn(_this.goodsListname[i].end_time));
                        }
                    });
                });
                this.stateBool = false;
            },
            //点击筛选来源
            screenSource:function(item){
                for(var b = 0;b < this.sourceCont.length;b++){
                    this.sourceCont[b].sourceChecked = false;
                }
                item.sourceChecked = true;
                var classi='',//分类
                    zhuangtai = '', //状态
                    paixu = '',//排序
                    _this = this;
                for(var i = 0;i < this.classifyList.length;i++){
                    if(this.classifyList[i].fal == 1){
                        classi = this.classifyList[i].id;
                    }
                }
                for(var a = 0;a < this.stateCont.length;a++){
                    if(this.stateCont[a].stateChecked == true){
                        zhuangtai = this.stateCont[a].id;
                    }
                }
                for(var c = 0;c < this.sortCont.length;c++){
                    if(this.sortCont[c].sortChecked == true){
                        paixu = this.sortCont[c].id;
                    }
                }
                axios.get('http://www.yiluzongheng.com/api/index/goodslist/?page=1&cates=' + classi + '&price='+ paixu +'&source='+ item.id +'&readytime=' + zhuangtai).then(function(res){
                    _this.goodsListname = res.data.data;
                    setInterval(function(){
                        _this.end_time = [];
                        for(var i = 0;i<_this.goodsListname.length;i++){
                            _this.end_time.push(timeFn(_this.goodsListname[i].end_time));
                        }
                    });
                });
                console.log('cates=' + classi + '&price='+ paixu +'&source='+ item.id +'&readytime=' + zhuangtai)
                this.sourceBool = false;
            },
            // 筛选排序
            screenSort:function(item){
                for(var b = 0;b < this.sortCont.length;b++){
                    this.sortCont[b].sortChecked = false;
                }
                item.sortChecked = true;
                var classi='',//分类
                    zhuangtai = '', //状态
                    laiyuan = '',//排序
                    _this = this;
                for(var i = 0;i < this.classifyList.length;i++){
                    if(this.classifyList[i].fal == 1){
                        classi = this.classifyList[i].id;
                    }
                }
                for(var a = 0;a < this.stateCont.length;a++){
                    if(this.stateCont[a].stateChecked == true){
                        zhuangtai = this.stateCont[a].id;
                    }
                }
                for(var c = 0;c < this.sourceCont.length;c++){
                    if(this.sourceCont[c].sortChecked == true){
                        laiyuan = this.sourceCont[c].id;
                    }
                }
                axios.get('http://www.yiluzongheng.com/api/index/goodslist/?page=1&cates=' + classi + '&price='+ item.id +'&source='+ laiyuan +'&readytime=' + zhuangtai).then(function(res){
                    _this.goodsListname = res.data.data;
                    setInterval(function(){
                        _this.end_time = [];
                        for(var i = 0;i<_this.goodsListname.length;i++){
                            _this.end_time.push(timeFn(_this.goodsListname[i].end_time));
                        }
                    });
                });
                this.sortBool = false;
            },

            //banner
            bannerImg:function(){
                var _this = this
                axios.get('/api/index/banner').then(function(res){

                   _this.goodsListname = res.data.data;
                    for(var i = 0;i<_this.goodsListname.length;i++){
                        _this.goodsListnames.push({src:_this.goodsListname[i].banner_img})
                    }
                    _this.$nextTick(function () {
                        swiperInit:{
                            var swiper = new Swiper('.swiper-container', {
                                slidesPerView: 1,
                                spaceBetween: 30,
                                loop: true,
                                autoplay : true,
                                pagination: {
                                    el: '.swiper-pagination',
                                    clickable: true,
                                },
                                observer:true,//修改swiper自己或子元素时，自动初始化swiper
                                observeParents:false,//修改swiper的父元素时，自动初始化swiper
                                onSlideChangeEnd: function(swiper){
                                    swiper.update();
                                    mySwiper.startAutoplay();
                                    mySwiper.reLoop();
                                }
                            });
                        }
                    })
                    console.log(_this.goodsListnames)
                }).catch(function(){
                    console.log('我就知道')
                })
            },

            // 商品列表
            goodsList:function(){
                var _this = this;
                this.claAll = true;
                axios.get("/api/index/goodslist?page=1").then(function(res){
                    _this.goodsListname = res.data.data;
                    console.log(res.data.data);
                    setInterval(function(){
                        _this.end_time = [];
                        for(var i = 0;i<_this.goodsListname.length;i++){
                            _this.end_time.push(timeFn(_this.goodsListname[i].end_time));
                        }
                    });
                }).catch(function(){
                    console.log('特么的')
                });
                this.feilei = false;
            }

        },
        created:function(){
            this.classify();
            this.bannerImg();
            this.goodsList();

            var scrollNum = 1;
            var _this = this;
            window.onscroll = function(){
                var scrollTop = document.documentElement.scrollTop||document.body.scrollTop;
                var windowHeight = document.documentElement.clientHeight || document.body.clientHeight;
                var scrollHeight = document.documentElement.scrollHeight||document.body.scrollHeight;
                if(scrollTop + windowHeight >= scrollHeight){
                    scrollNum++;
                    var laiyuan = '', //来源
                        paixu = '',//排序
                        classi = '',
                        zhuangtai='';
                    for(var i = 0;i < _this.classifyList.length;i++){
                        if(_this.classifyList[i].fal == 1){
                            classi = _this.classifyList[i].id;
                        }
                    }
                    for(var a = 0;a < _this.sourceCont.length;a++){
                        if(_this.sourceCont[a].sourceChecked == true){
                            laiyuan = _this.sourceCont[a].id;
                        }
                    }
                    for(var c = 0;c < _this.sortCont.length;c++){
                        if(_this.sortCont[c].sortChecked == true){
                            paixu = _this.sortCont[c].id;
                        }
                    }
                    for(var a = 0;a < _this.stateCont.length;a++){
                        if(_this.stateCont[a].stateChecked == true){
                            zhuangtai = _this.stateCont[a].id;
                        }
                    }
                    axios.get("/api/index/goodslist?page=" + scrollNum +"cates=" + classi + '&price='+ paixu +'&source='+ laiyuan +'&readytime=' + zhuangtai).then(function(res){
                        for(var i = 0; i < res.data.data.length; i++){
                            _this.goodsListname.push(res.data.data[i])
                        }
                        setInterval(function(){
                            _this.end_time = [];
                            for(var i = 0;i<_this.goodsListname.length;i++){
                                _this.end_time.push(timeFn(_this.goodsListname[i].end_time));
                            }
                        });
                    }).catch(function(){
                        console.log('特么的')
                    })
                }
            }
        },

    })
</script>
</body>
</html>