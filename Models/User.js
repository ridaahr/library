export class User {
  constructor(id, name, email, role, penaltyUntil = null) {
    this.id = id;
    this.name = name;
    this.email = email;
    this.role = role;
    this.penaltyUntil = penaltyUntil;
  }

  hasPenalty() {
    if (!this.penaltyUntil) return false;
    return new Date(this.penaltyUntil) > new Date();
  }
}
