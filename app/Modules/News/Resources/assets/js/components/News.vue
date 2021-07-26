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
                            <span class="input-group-addon"
                                ><span
                                    class="input-group-text fa fa-calendar"
                                    aria-hidden="true"
                                ></span
                            ></span>
                            <input
                                id="datestartshort"
                                type="text"
                                class="date-pick form-control"
                                name="start"
                                placeholder="YYYY-MM-DD"
                                data-start=""
                                value=""
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
                            <span class="input-group-addon"
                                ><span
                                    class="input-group-text fa fa-calendar"
                                    aria-hidden="true"
                                ></span
                            ></span>
                            <input
                                id="datestopshort"
                                type="text"
                                class="date-pick form-control"
                                name="stop"
                                placeholder="YYYY-MM-DD"
                                data-stop=""
                                value=""
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
                            v-on:keyup="read"
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
                        />
                    </div>
                </div>
                <div class="form-group row" id="TR_search">
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
                </div>
                <span id="TAB_search_action"></span>
                <span id="TAB_add_action"></span>
            </fieldset>
        </form>
        <p id="matchingnews">Found {{ total }} matching News Articles</p>
        <news-article
            v-for="article in articles"
            v-bind="article"
            :key="article.id"
            @update="update"
            @delete="del"
        ></news-article>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item">
                    <a class="page-link" href="#">Previous</a>
                </li>
                <li class="page-item">
                    <span class="page-link" @click="read">1</span>
                </li>
                <li class="page-item">
                    <span class="page-link" @click="read">2</span>
                </li>
                <li class="page-item">
                    <span class="page-link" @click="read">3</span>
                </li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item">
                    <span class="page-link" @click="read">1</span>
                </li>
                <li class="page-item">
                    <span class="page-link" @click="read">2</span>
                </li>
                <li class="page-item">
                    <span class="page-link" @click="read">3</span>
                </li>
            </ul>
        </nav>
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
            working: false,
            total: 0,
            keywords: ""
        };
    },
    methods: {
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
        read(evt) {
            console.log("Retrieving articles...");
            // Get page number clicked (if any)
            let pageRequest =
                typeof evt !== "undefined"
                    ? parseInt(evt.target.innerHTML)
                    : pageRequest;
            this.mute = true;
            window.axios
                .get(this.ROOT_URL + "/api/news", {
                    params: {
                        search: this.keywords,
                        page: pageRequest
                    }
                })
                .then(({ data }) => {
                    this.articles = [];
                    data.data.forEach(datum => {
                        this.articles.push(datum); //new Article(datum));
                    });
                    this.total = data.meta.total;
                    // Set the list of pagination numbers
                    // paginationList = [];
                    // if (pageRequest >= 21)
                    //     paginationList.push(pageRequest - 20);
                    // const tenPageSequenceStart =
                    // if (pageRequest )
                    this.mute = false;
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
