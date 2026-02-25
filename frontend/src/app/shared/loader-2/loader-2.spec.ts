import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Loader2 } from './loader-2';

describe('Loader2', () => {
  let component: Loader2;
  let fixture: ComponentFixture<Loader2>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Loader2]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Loader2);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
