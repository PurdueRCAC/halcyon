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
                                v-model="startDate"
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
                                v-model="stopDate"
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
                            v-model="selectedNewsType"
                        >
                            <option v-for="newsType in newsTypeOptions" :key="newsType">
                                {{ newsType }}
                            </option>
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
                <span id="TAB_search_action"></span>
                <span id="TAB_add_action"></span>
            </fieldset>
        </form>

        <template v-if="isFetchingData">
            <strong>Loading News Articles...</strong>
        </template>
        <template v-else>
            <p id="matchingnews">Found {{ total }} matching News Articles</p>
            <news-article-list-component
                v-for="article in articles"
                v-bind="article"
                :key="article.id"
                @update="update"
                @delete="del"
            ></news-article-list-component>

            <nav aria-label="navigation">
                <ul class="pagination">
                    <li
                        v-for="(page, index) of paginationList"
                        :key="index"
                        :class="['page-item', (pageRequest === page ? 'active' : '')]"
                        style="cursor: pointer"
                    >
                        <strong>
                            <span class="page-link" @click="handlePaginationEvent">{{ page }}</span>
                        </strong>
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
import NewsArticleListComponent from "./NewsArticleListComponent.vue";
export default {
    data() {
        return {
            articles: [],
            paginationList: [],
            newsTypeOptions: [
                "All",
                "Announcements",
                "Events",
                "Outages and Maintenance",
                "Science Highlights"
            ],
            selectedNewsType: "All",
            selectedNewsType_id: null,
            pageRequest: 1,
            farthestPage: 1,
            limit: 20,
            working: false,
            total: 0,
            keywords: "",
            isFetchingData: true,
            id: null,
            startDate: "",
            stopDate: "",
            start: null,
            stop: null
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
                    }
                }
                this.read(this.pageRequest);
            }
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
                        stop: this.stop,
                        type: this.selectedNewsType_id
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
        startDate(date) {
            this.start = date + "T";
            this.handleFormEvent();
        },
        stopDate(date) {
            this.stop = date + "T";
            this.handleFormEvent();
        },
        selectedNewsType(newValue, previousValue) {
            if (newValue !== previousValue) {
                switch (newValue) {
                    case "All":
                        this.selectedNewsType_id = null;
                        break;
                    case "Announcements":
                        this.selectedNewsType_id = 2;
                        break;
                    case "Events":
                        this.selectedNewsType_id = 4;
                        break;
                    case "Outages and Maintenance":
                        this.selectedNewsType_id = 1;
                        break;
                    case "Science Highlights":
                        this.selectedNewsType_id = 3;
                        break;
                }
                this.handleFormEvent();
            }
        }
    },
    components: {
        NewsArticleListComponent
    },
    created() {
        this.read();
    },
    mounted() {
        this.read();
    }
};
</script>