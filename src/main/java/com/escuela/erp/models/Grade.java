package com.escuela.erp.models;

import jakarta.persistence.*;
import java.math.BigDecimal;

@Entity
@Table(name = "grades", uniqueConstraints = {@UniqueConstraint(columnNames = {"student_id", "subject_id", "period"})})
public class Grade {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(optional = false)
    @JoinColumn(name = "student_id", nullable = false)
    private Student student;

    @ManyToOne(optional = false)
    @JoinColumn(name = "subject_id", nullable = false)
    private Subject subject;

    @Column(nullable = false)
    private Integer period;

    @Column(name = "exam_grade", precision = 3, scale = 2)
    private BigDecimal examGrade = BigDecimal.ZERO;

    @Column(name = "workshop_grade", precision = 3, scale = 2)
    private BigDecimal workshopGrade = BigDecimal.ZERO;

    @Column(name = "project_grade", precision = 3, scale = 2)
    private BigDecimal projectGrade = BigDecimal.ZERO;

    @Column(name = "final_grade", precision = 3, scale = 2)
    private BigDecimal finalGrade = BigDecimal.ZERO;

    @Column(columnDefinition = "TEXT")
    private String comments;

    public Grade() {}

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }
    public Student getStudent() { return student; }
    public void setStudent(Student student) { this.student = student; }
    public Subject getSubject() { return subject; }
    public void setSubject(Subject subject) { this.subject = subject; }
    public Integer getPeriod() { return period; }
    public void setPeriod(Integer period) { this.period = period; }
    public BigDecimal getExamGrade() { return examGrade; }
    public void setExamGrade(BigDecimal examGrade) { this.examGrade = examGrade; }
    public BigDecimal getWorkshopGrade() { return workshopGrade; }
    public void setWorkshopGrade(BigDecimal workshopGrade) { this.workshopGrade = workshopGrade; }
    public BigDecimal getProjectGrade() { return projectGrade; }
    public void setProjectGrade(BigDecimal projectGrade) { this.projectGrade = projectGrade; }
    public BigDecimal getFinalGrade() { return finalGrade; }
    public void setFinalGrade(BigDecimal finalGrade) { this.finalGrade = finalGrade; }
    public String getComments() { return comments; }
    public void setComments(String comments) { this.comments = comments; }
}
