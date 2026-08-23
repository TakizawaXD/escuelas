package com.escuela.erp.models;

import jakarta.persistence.*;
import lombok.*;
import java.time.LocalDate;

@Entity
@Table(name = "students")
@Data @NoArgsConstructor @AllArgsConstructor @Builder
public class Student {
    @Id @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @OneToOne(fetch = FetchType.EAGER, optional = false)
    @JoinColumn(name = "user_id", nullable = false, unique = true)
    private User user;

    @ManyToOne(fetch = FetchType.EAGER, optional = false)
    @JoinColumn(name = "course_id", nullable = false)
    private Course course;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "parent_user_id")
    private User parentUser;

    @Column(name = "birth_date", nullable = false)
    private LocalDate birthDate;

    @Column(length = 200)
    private String address;

    @Column(name = "photo_url", length = 255)
    private String photoUrl;

    @Builder.Default
    @Column(precision = 4, scale = 2)
    private Double gpa = 0.0;

    @Column(name = "academic_status", length = 30)
    @Builder.Default
    private String academicStatus = "ACTIVO";
}
