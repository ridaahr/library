class Book {
    constructor(id, title, author, category, total, available, timesLoaned = 0) {
        this.id = id;
        this.title = title;
        this.author = author;
        this.category = category;
        this.total = total;
        this.available = available;
        this.timesLoaned = timesLoaned;
    }
}