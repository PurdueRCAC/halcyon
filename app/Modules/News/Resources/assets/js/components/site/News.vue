<template>
    <div id="news">
        <form method="get" action="" class="editform">
            <fieldset>
                <legend>Search News</legend>
                <div class="form-group row" id="TR_date">
                    <label for="datestartshort" class="col-sm-2 col-form-label"
                        >Date from</label
                    >
                    <div class="col-sm-4">
                        <div class="input-group">
                            <input
                                id="datestartshort"
                                type="date"
                                class="form-control"
                                name="start"
                                value=""
                                v-model="start"
                            />
                            <input
                                id="timestartshort"
                                type="text"
                                class="time-pick form-control hide"
                                name="starttime"
                                value=""
                            />
                        </div>
                    </div>
                    <label
                        for="datestopshort"
                        class="col-sm-2 col-form-label align-right"
                        >Date to</label
                    >
                    <div class="col-sm-4">
                        <div class="input-group" id="enddate">
                            <input
                                id="datestopshort"
                                type="date"
                                class="form-control"
                                name="stop"
                                value=""
                                v-model="stop"
                            />
                            <input
                                id="timestopshort"
                                type="text"
                                class="time-pick form-control hide"
                                name="stoptime"
                                value=""
                            />
                        </div>
                    </div>
                </div>
                <div class="form-group row" id="TR_newstype">
                    <label for="newstype" class="col-sm-2 col-form-label"
                        >News Type</label
                    >
                    <div class="col-sm-10">
                        <select
                            id="newstype"
                            name="newstype"
                            class="form-control"
                        >
                            <option id="OPTION_all" name="all" value="-1"
                                >All</option
                            >
                        </select>
                    </div>
                </div>
                <div class="form-group row" id="TR_keywords">
                    <label for="keywords" class="col-sm-2 col-form-label"
                        >Keywords</label
                    >
                    <div class="col-sm-10">
                        <input
                            type="text"
                            v-model="keywords"
                            @keyup="handleFormEvent"
                            name="keyword"
                            id="keywords"
                            size="45"
                            class="form-control"
                            value=""
                        />
                    </div>
                </div>
                <div class="form-group row" id="TR_resource">
                    <label for="newsresource" class="col-sm-2 col-form-label"
                        >Resource</label
                    >
                    <div class="col-sm-10">
                        <input
                            name="resource"
                            id="newsresource"
                            size="45"
                            class="form-control"
                            value=""
                            data-uri="resource/%s"
                        />
                    </div>
                </div>
                <div class="form-group row" id="TR_location">
                    <label for="location" class="col-sm-2 col-form-label"
                        >Location</label
                    >
                    <div class="col-sm-10">
                        <input
                            name="location"
                            id="location"
                            type="text"
                            size="45"
                            maxlength="32"
                            class="form-control"
                        />
                    </div>
                </div>
                <div class="form-group row" id="TR_id">
                    <label for="id" class="col-sm-2 col-form-label"
                        >NEWS#</label
                    >
                    <div class="col-sm-10">
                        <input
                            name="id"
                            type="text"
                            id="id"
                            size="45"
                            class="form-control"
                            value=""
                            v-model.number="id"
                            @keyup="handleFormEvent"
                        />
                    </div>
                </div>
                <!-- <div class="form-group row" id="TR_search">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-10 offset-sm-10">
                        <input
                            type="submit"
                            class="btn btn-primary"
                            value="Search"
                            id="INPUT_search"
                        />
                        <input
                            type="reset"
                            class="btn btn-default"
                            value="Clear"
                            id="INPUT_clear"
                        />
                    </div>
                </div> -->
                <span id="TAB_search_action"></span>
                <span id="TAB_add_action"></span>
            </fieldset>
        </form>

        <template v-if="isFetchingData">
            <strong>Loading News Articles...</strong>
        </template>
        <template v-else>
            <p id="matchingnews">Found {{ total }} matching News Articles</p>
            <news-article
                v-for="article in articles"
                v-bind="article"
                :key="article.id"
                @update="update"
                @delete="del"
            ></news-article>

            <nav aria-label="navigation">
                <ul class="pagination">
                    <li
                        v-for="page of paginationList"
                        :key="page"
                        :class="['page-item', (pageRequest === page ? 'active' : '')]"
                    >
                        <span class="page-link" @click="handlePaginationEvent">
                            <strong>{{ page }}</strong>
                        </span>
                    </li>
                </ul>
            </nav>
        </template>
    </div>
</template>
<script>
/*function Article({ id, color, name}) {
		this.id = id;
		this.color = color;
		this.name = name;
	}*/
import NewsArticle from "./NewsArticle.vue";
export default {
    data() {
        return {
            articles: [],
            paginationList: [],
            pageRequest: 1,
            farthestPage: 1,
            limit: 20,
            working: false,
            total: 0,
            keywords: "",
            isFetchingData: true,
            id: null,
            start: "",
            stop: ""
        };
    },
    methods: {
        // Helper and event handler methods
        setPaginationList(curPage) {
            this.paginationList = [];
            this.paginationList.push('<');
            this.paginationList.push(1);
            if (curPage > 6)
                this.paginationList.push('...');
            
            const startPageList = curPage <= 6
                ? 2
                : Math.max(Math.min(curPage - 4, this.farthestPage - 8), 2);
            const endPageList = (curPage + 5) >= this.farthestPage
                ? this.farthestPage - 1
                : Math.min(Math.max(curPage + 4, 9), this.farthestPage - 1);
            console.log("start page list: " + startPageList);
            console.log("end page list: " + endPageList);
            for (let pageNum = startPageList; pageNum <= endPageList; pageNum++)
                this.paginationList.push(pageNum);
            if (curPage < (this.farthestPage - 5))
                this.paginationList.push('...');
            if (this.farthestPage > 1)
                this.paginationList.push(this.farthestPage);
            this.paginationList.push('>');
        },
        handleFormEvent(evt = undefined) {
            if (evt !== undefined)
                evt.preventDefault();
            this.isFetchingData = true;
            this.read();
        },
        handlePaginationEvent(evt) {
            evt.preventDefault();
            this.isFetchingData = true;
            
            if (typeof evt !== "undefined") {
                if (
                    parseInt(evt.target.innerHTML) != NaN &&
                    parseInt(evt.target.innerHTML) >= 1 &&
                    parseInt(evt.target.innerHTML) <= this.farthestPage
                ) {
                    this.pageRequest = parseInt(evt.target.innerHTML);
                } else {
                    switch (String(evt.target.innerHTML)) {
                        case "&lt;&lt;":
                            this.pageRequest = 1;
                            break;
                        case "&gt;&gt;":
                            this.pageRequest = this.farthestPage;
                            break;
                        case "&lt;":
                            if (this.pageRequest > 1) {
                                this.pageRequest -= 1;
                            }
                            break;
                        case "&gt;":
                            if (this.pageRequest < this.farthestPage) {
                                this.pageRequest += 1;
                            }
                            break;
                        default:
                    }
                }
            }
            this.read(this.pageRequest);
        },
        // HTTP Request methods
        create() {
            console.log("Creating article");
            this.mute = true;
            window.axios
                .post(this.ROOT_URL + "/api/news/create")
                .then(({ data }) => {
                    this.articles.push(datum); //new Article(data));
                    this.mute = false;
                });
        },
        read(pageRequest = 1) {
            console.log("Retrieving articles...");
            this.pageRequest = pageRequest;
            this.mute = true;
            window.axios
                .get(this.ROOT_URL + "/api/news", {
                    params: {
                        search: this.keywords,
                        page: this.pageRequest,
                        limit: this.limit,
                        id: this.id,
                        start: this.start,
                        stop: this.stop
                    }
                })
                .then(({ data }) => {
                    this.articles = [];
                    data.data.forEach(datum => {
                        this.articles.push(datum); //new Article(datum));
                    });
                    this.total = data.meta.total;
                    this.farthestPage = Math.ceil(this.total / this.limit);
                    this.setPaginationList(this.pageRequest);
                    this.mute = false;
                    this.isFetchingData = false;
                });
        },
        update(id, color) {
            console.log("Updating article #" + id);
            this.mute = true;
            window.axios
                .put(`${this.ROOT_URL}/api/news/${id}`, { color })
                .then(() => {
                    this.articles.find(datum => datum.id === id).color = color;
                    this.mute = false;
                });
        },
        del(id) {
            console.log("Deleting article #" + id);
            this.mute = true;
            window.axios.delete(`${this.ROOT_URL}/api/news/${id}`).then(() => {
                let index = this.articles.findIndex(datum => datum.id === id);
                this.articles.splice(index, 1);
                this.mute = false;
            });
        }
    },
    watch: {
        mute(val) {
            document.getElementById("mute").className = val ? "on" : "";
        },
        start(date) {
            console.log("startDate: " + date);
        },
        stop(date) {
            console.log("stopDate: " + date);
        }
    },
    components: {
        NewsArticle
    },
    created() {
        this.read();
    },
    mounted() {
        this.read();
    }
};
</script>