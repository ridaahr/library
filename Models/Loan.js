class Loan {
  constructor(id, userId, bookId, loanDate, returnDate, returned = false) {
    this.id = id;
    this.userId = userId;
    this.bookId = bookId;
    this.loanDate = new Date(loanDate);
    this.returnDate = new Date(returnDate);
    this.returned = returned;
  }

  isLate() {
    return !this.returned && new Date() > this.returnDate;
  }
}
